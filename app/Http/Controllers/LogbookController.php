<?php
namespace App\Http\Controllers;
use App\Contracts\LogbookRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
class LogbookController extends Controller
{
    public function __construct(private LogbookRepository $records) {}
    public function login() { return view('pages.login'); }
    public function authenticate(Request $request)
    {
        $data = $request->validate(['login_identifier'=>'required|string', 'password'=>'required|string'], ['required'=>'Sila lengkapkan :attribute.']);
        $accounts = ['pelajar@demo.kvsp1.local'=>['id'=>'student-1','role'=>'student','name'=>'Nur Aisyah binti Azlan'], 'penyelia@demo.kvsp1.local'=>['id'=>'supervisor-1','role'=>'supervisor','name'=>'Pn. Faridah binti Hassan']];
        $actor = $accounts[$data['login_identifier']] ?? null;
        if (! $actor || ! hash_equals('Demo-PTA1!', $data['password'])) return back()->withErrors(['login_identifier'=>'ID atau kata laluan tidak sepadan. Gunakan akaun contoh yang disediakan.'])->onlyInput('login_identifier');
        $request->session()->regenerate(); session()->put('actor', $actor);
        return $actor['role']==='supervisor' ? redirect()->route('projects') : redirect()->route('document', ['project'=>'projek-001','page'=>'profile']);
    }
    public function logout(Request $request)
    {
        // Preserve synthetic drafts across demo role changes; never use as production logout.
        session()->forget('actor'); $request->session()->regenerate(); $request->session()->regenerateToken();
        return redirect()->route('login');
    }
    public function projects(Request $request)
    {
        abort_unless(session('actor.role')==='supervisor', 403);
        $projects = $this->records->assigned(session('actor'));
        $q = trim((string)$request->query('q', ''));
        if ($q !== '') $projects = array_filter($projects, fn($p)=>mb_stripos($p['student']['full_name'].' '.$p['project']['project_title'].' '.$p['project']['programme_name'], $q)!==false);
        return view('pages.projects', compact('projects','q') + ['page'=>'projects','title'=>'Projek di bawah seliaan']);
    }
    public function show(Request $request, string $project, string $page)
    {
        $phase = $request->routeIs('pta2.*') ? 2 : 1;
        abort_unless(array_key_exists($page,config('logbook.pages')),404);
        abort_if($page==='evaluation' && session('actor.role')!=='supervisor',403);
        $oldProject=$project;
        $project=$this->canonicalProject($project);
        $record = $this->records->get($project,session('actor'));
        if ($oldProject!==$project || $request->routeIs('pta2.legacy') || ($request->routeIs('document') && in_array($page,['schedule','meeting']))) {
            $route = in_array($page,['schedule','meeting']) ? 'pta'.$phase.'.document' : 'document';
            return redirect()->route($route, ['project'=>$project,'page'=>$page]+$request->query());
        }
        if ($phase===2) $record['meetings']=$record['meetings_pta2']??[];
        $section = filter_var($request->query('section',1), FILTER_VALIDATE_INT);
        if ($page==='proposal') abort_unless($section && $section>=1 && $section<=13,404);
        $meetingId = (int)$request->query('meeting', array_key_first($record['meetings']) ?? 0);
        $meeting = $record['meetings'][$meetingId] ?? null;
        if ($page==='meeting' && $request->has('meeting')) abort_unless($meeting,404);
        $title = in_array($page,['schedule','meeting']) ? config("logbook.pages.$page")[0].' PTA '.$phase : config("logbook.pages.$page")[0];
        $view = $page==='report' ? 'pages.empty-document' : "pages.$page";
        return view($view, compact('record','project','page','phase','section','meeting','meetingId','title'));
    }
    public function save(Request $request, string $project, string $page)
    {
        $phase = $request->routeIs('pta2.*') ? 2 : 1;
        $project=$this->canonicalProject($project);
        $record = $this->records->get($project,session('actor'));
        abort_unless(in_array($page,['profile','proposal','schedule','meeting','evaluation']),404);
        abort_unless(session('actor.role')===($page==='evaluation'?'supervisor':'student'),403);
        $meetingsKey = $phase===2 ? 'meetings_pta2' : 'meetings';
        $record[$meetingsKey] ??= [];
        $query=[];
        if ($page==='profile') {
            $rules = [];
            foreach (array_keys($record['student']) as $key) $rules["student.$key"] = 'nullable|string';
            foreach ([1,2] as $n) foreach (['name','telephone','relationship'] as $key) $rules["contacts.$n.$key"]='nullable|string';
            $rules['project.programme_code']='nullable|string';
            $rules['project.project_title']='nullable|string';
            $rules['student.identity_number']='nullable|string|max:32'; $rules['student.telephone']='nullable|string|max:32'; $rules['student.college_telephone']='nullable|string|max:32'; $rules['student.college_email']='nullable|email';
            $data=$request->validate($rules);
            foreach ($data['student'] ?? [] as $k=>$v) $record['student'][$k]=$v ?? '';
            foreach ($data['project']??[] as $k=>$v) $record['project'][$k]=$v??'';
            if (array_key_exists('programme',$data['student']??[])) $record['project']['programme_name']=$record['student']['programme'];
            foreach ($data['contacts'] ?? [] as $n=>$contact) foreach ($contact as $k=>$v) $record['contacts'][$n][$k]=$v ?? '';
        } elseif ($page==='proposal') {
            $data=$request->validate(['section'=>'required|integer|between:1,13','content'=>'nullable|string','project.programme_name'=>'nullable|string','project.course_code'=>'nullable|string','project.project_title'=>'nullable|string','attachment'=>'nullable|file|mimes:pdf,png,jpg,jpeg|max:10240']);
            $n=(int)$data['section'];
            if ($n!==13) $record['sections'][$n]=$data['content']??'';
            if ($n===13) {
                $photos=$request->validate(['photos'=>'nullable|array','photos.*.caption'=>'nullable|string','photos.*.linked_section'=>'nullable|integer|between:1,12','photos.*.image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:10240']);
                foreach ($photos['photos']??[] as $i=>$photo) {
                    abort_unless(ctype_digit((string)$i),422);
                    $existing=$record['attachments'][13][$i]??null;
                    if ($request->hasFile("photos.$i.image")) {
                        $file=$request->file("photos.$i.image");
                        $existing=['path'=>$file->store('demo-attachments/'.session()->getId(),'local'),'name'=>$file->getClientOriginalName(),'is_photo'=>true];
                    }
                    if ($existing) {
                        $existing['caption']=$photo['caption']??'';
                        $existing['linked_section']=isset($photo['linked_section'])?(int)$photo['linked_section']:null;
                        if (!empty($existing['is_photo'])) $record['attachments'][13][$i]=$existing;
                    } elseif (!empty($photo['caption']) || !empty($photo['linked_section'])) {
                        throw \Illuminate\Validation\ValidationException::withMessages(["photos.$i.image"=>'Sila pilih foto untuk lampiran ini.']);
                    }
                }
            }
            foreach ($data['project']??[] as $k=>$v) $record['project'][$k]=$v ?? '';
            if ($request->hasFile('attachment')) {
                $file=$request->file('attachment'); $path=$file->store('demo-attachments/'.session()->getId(),'local');
                $record['attachments'][$n][]=['path'=>$path,'name'=>$file->getClientOriginalName()];
            }
            $query=['section'=>$n];
        } elseif ($page==='schedule') {
            $data=$request->validate(['meeting_date'=>'nullable|date_format:Y-m-d','activity'=>'nullable|string']);
            $n=$record[$meetingsKey] ? max(array_keys($record[$meetingsKey]))+1 : 1;
            $record[$meetingsKey][$n]=['meeting_number'=>$n,'meeting_date'=>$data['meeting_date']??'','activity'=>$data['activity']??'','day_label'=>'','meeting_time'=>'','supervisor_name'=>$record['supervisor_name'],'department_programme'=>$record['project']['programme_name'],'discussion_summary'=>'','next_action_plan'=>''];
            $page='meeting'; $query=['meeting'=>$n];
        } elseif ($page==='meeting') {
            $data=$request->validate(['meeting'=>'required|integer','meeting_date'=>'nullable|date_format:Y-m-d','activity'=>'nullable|string','day_label'=>'nullable|string','meeting_time'=>'nullable|date_format:H:i','supervisor_name'=>'nullable|string','department_programme'=>'nullable|string','discussion_summary'=>'nullable|string','next_action_plan'=>'nullable|string']);
            $n=(int)$data['meeting']; abort_unless(isset($record[$meetingsKey][$n]),404); unset($data['meeting']);
            foreach($data as $k=>$v) $record[$meetingsKey][$n][$k]=$v??'';
            $query=['meeting'=>$n];
        } elseif ($page==='evaluation') {
            $data=$request->validate(['competency_decision'=>['nullable',Rule::in(['Terampil','Tidak Terampil'])],'decision_explanation'=>'nullable|string']);
            $record['evaluation']=['competency_decision'=>$data['competency_decision']??'','decision_explanation'=>$data['decision_explanation']??''];
        }
        if ($page==='proposal') {
            if (isset($data['project']) && array_key_exists('programme_name',$data['project'])) $record['student']['programme']=$record['project']['programme_name'];
        }
        $this->records->save($project,session('actor'),$record);
        $route=in_array($page,['schedule','meeting']) ? 'pta'.$phase.'.document' : 'document';
        return redirect()->route($route,['project'=>$project,'page'=>$page]+$query)->with('success','Draf disimpan dalam sesi pratonton ini.');
    }
    public function attachment(Request $request, string $project, int $section, int $index)
    {
        $project=$this->canonicalProject($project);
        $r=$this->records->get($project,session('actor')); $attachment=$r['attachments'][$section][$index]??abort(404);
        abort_unless(Storage::disk('local')->exists($attachment['path']),404);
        if ($request->boolean('preview')) {
            abort_unless(!empty($attachment['is_photo']),404);
            return response()->file(Storage::disk('local')->path($attachment['path']),['X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store']);
        }
        return Storage::disk('local')->download($attachment['path'],$attachment['name'],['X-Content-Type-Options'=>'nosniff']);
    }
    private function canonicalProject(string $project): string
    {
        return ['pta-001'=>'projek-001','pta-002'=>'projek-002'][$project] ?? $project;
    }
}
