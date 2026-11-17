<?php
namespace App\Http\Controllers;

use App\Contracts\LogbookRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class SlideController extends Controller
{
    public function __construct(private LogbookRepository $records) {}

    private function project(string $project): string
    {
        return ['pta-001'=>'projek-001','pta-002'=>'projek-002'][$project] ?? $project;
    }

    private function studentRecord(string $project): array
    {
        abort_unless(session('actor.role')==='student',403);
        return $this->records->get($project,session('actor'));
    }

    private function slide(array $record, string $item): array
    {
        foreach ([1,2] as $phase) {
            if (isset($record['slides'][$phase][$item])) return [$phase,$record['slides'][$phase][$item]];
        }
        abort(404);
    }

    public function store(Request $request, string $project)
    {
        $project=$this->project($project);
        $record=$this->studentRecord($project);
        $data=$request->validate([
            'phase'=>'required|integer|in:1,2',
            'format'=>['required',Rule::in(['pptx','pdf','images','canva'])],
        ]);
        $phase=(int)$data['phase'];
        $format=$data['format'];
        $item=['format'=>$format,'pages'=>[]];

        if ($format==='canva') {
            $link=$request->validate(['canva_url'=>['required','url:http,https','max:2048']])['canva_url'];
            $host=strtolower((string)parse_url($link,PHP_URL_HOST));
            if (!($host==='canva.com' || str_ends_with($host,'.canva.com')) || parse_url($link,PHP_URL_SCHEME)!=='https') {
                throw ValidationException::withMessages(['canva_url'=>'Gunakan pautan HTTPS daripada canva.com.']);
            }
            $item['url']=$link;
        } elseif ($format==='images') {
            $files=$this->validateImages($request);
            foreach ($files as $file) $item['pages'][]=$this->storeFile($file,$project);
        } else {
            $rules=$format==='pptx' ? 'required|file|mimes:pptx|extensions:pptx|max:25600' : 'required|file|mimes:pdf|extensions:pdf|max:25600';
            $file=$request->validate(['slide_file'=>$rules])['slide_file'];
            $item['pages'][]=$this->storeFile($file,$project);
        }

        $record['slides'][$phase][(string)Str::uuid()]=$item;
        $this->records->save($project,session('actor'),$record);
        return redirect()->to(route('document',['project'=>$project,'page'=>'slides']).'#pta-'.$phase)->with('success','Slaid disimpan dalam sesi pratonton ini.');
    }

    public function addPages(Request $request, string $project, string $item)
    {
        $project=$this->project($project);
        $record=$this->studentRecord($project);
        [$phase,$slide]=$this->slide($record,$item);
        abort_unless($slide['format']==='images',404);
        foreach ($this->validateImages($request) as $file) $record['slides'][$phase][$item]['pages'][]=$this->storeFile($file,$project);
        $this->records->save($project,session('actor'),$record);
        return redirect()->to(route('document',['project'=>$project,'page'=>'slides']).'#pta-'.$phase)->with('success','Halaman gambar ditambah.');
    }

    public function movePage(Request $request, string $project, string $item, int $index)
    {
        $project=$this->project($project);
        $record=$this->studentRecord($project);
        [$phase,$slide]=$this->slide($record,$item);
        abort_unless($slide['format']==='images' && isset($slide['pages'][$index]),404);
        $direction=$request->validate(['direction'=>['required',Rule::in(['up','down'])]])['direction'];
        $target=$index+($direction==='up'?-1:1);
        abort_unless(isset($slide['pages'][$target]),404);
        [$record['slides'][$phase][$item]['pages'][$index],$record['slides'][$phase][$item]['pages'][$target]]
            =[$slide['pages'][$target],$slide['pages'][$index]];
        $this->records->save($project,session('actor'),$record);
        return redirect()->to(route('document',['project'=>$project,'page'=>'slides']).'#pta-'.$phase)->with('success','Susunan halaman dikemas kini.');
    }

    public function file(Request $request, string $project, string $item, int $index)
    {
        $project=$this->project($project);
        $record=$this->records->get($project,session('actor'));
        [, $slide]=$this->slide($record,$item);
        $file=$slide['pages'][$index]??abort(404);
        abort_unless(Storage::disk('local')->exists($file['path']),404);
        if ($request->boolean('preview')) {
            abort_unless($slide['format']==='images',404);
            return response()->file(Storage::disk('local')->path($file['path']),['X-Content-Type-Options'=>'nosniff','Cache-Control'=>'private, no-store']);
        }
        return Storage::disk('local')->download($file['path'],$file['name'],['X-Content-Type-Options'=>'nosniff']);
    }

    private function validateImages(Request $request): array
    {
        $data=$request->validate(['images'=>'required|array|min:1','images.*'=>'required|image|mimes:jpg,jpeg,png|extensions:jpg,jpeg,png|max:25600']);
        return $data['images'];
    }

    private function storeFile($file, string $project): array
    {
        return ['path'=>$file->store('demo-slides/'.session()->getId().'/'.$project,'local'),'name'=>$file->getClientOriginalName()];
    }
}
