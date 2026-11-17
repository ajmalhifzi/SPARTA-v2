<?php
namespace App\Repositories;
use App\Contracts\LogbookRepository;
/** Synthetic local-only records. No SQL, migrations, or production identity assumptions. */
class SessionLogbookRepository implements LogbookRepository
{
    private function records(): array
    {
        if (! session()->has('demo_records')) {
            $student = ['full_name'=>'Nur Aisyah binti Azlan', 'identity_number'=>'', 'year_semester'=>'Tahun 2 / Semester 3', 'programme'=>'Teknologi Sistem Komputer dan Rangkaian', 'telephone'=>'', 'address'=>'', 'college_name'=>'Kolej Vokasional Sungai Petani 1', 'college_address'=>'', 'college_telephone'=>'', 'college_email'=>''];
            $project = ['programme_name'=>'Teknologi Sistem Komputer dan Rangkaian', 'course_code'=>'', 'programme_code'=>'', 'project_title'=>'Sistem Tempahan Peralatan Makmal'];
            $sections = array_fill(1, 13, '');
            $sections[1] = 'Projek ini mencadangkan sebuah sistem tempahan peralatan makmal bagi memudahkan rekod pinjaman dan pemulangan peralatan. Pelajar boleh menyemak ketersediaan peralatan, manakala penyelaras makmal dapat merujuk rekod penggunaan di satu tempat.';
            $sections[2] = 'Rekod tempahan secara manual menyukarkan semakan ketersediaan peralatan dan pencarian semula rekod pinjaman.';
            $sections[3] = "Membangunkan antara muka tempahan peralatan makmal.\nMenyediakan rekod pinjaman dan pemulangan yang mudah dirujuk.";
            $base = ['id'=>'projek-001', 'student_user'=>'student-1', 'supervisor_user'=>'supervisor-1', 'supervisor_name'=>'Pn. Faridah binti Hassan', 'student'=>$student, 'project'=>$project, 'contacts'=>[1=>['name'=>'','telephone'=>'','relationship'=>''],2=>['name'=>'','telephone'=>'','relationship'=>'']], 'sections'=>$sections, 'attachments'=>[], 'meetings'=>[
                1=>['meeting_number'=>1,'meeting_date'=>'2026-10-26','activity'=>'Perbincangan cadangan tajuk projek','day_label'=>'Isnin','meeting_time'=>'10:00','supervisor_name'=>'Pn. Faridah binti Hassan','department_programme'=>'Teknologi Sistem Komputer dan Rangkaian','discussion_summary'=>"Membincangkan cadangan sistem tempahan peralatan makmal.\nMengenal pasti pengguna sistem dan masalah yang ingin diselesaikan.",'next_action_plan'=>'Memperincikan pernyataan masalah dan objektif projek.'],
                2=>['meeting_number'=>2,'meeting_date'=>'2026-11-02','activity'=>'Semakan pernyataan masalah dan objektif','day_label'=>'Isnin','meeting_time'=>'10:00','supervisor_name'=>'Pn. Faridah binti Hassan','department_programme'=>'Teknologi Sistem Komputer dan Rangkaian','discussion_summary'=>'','next_action_plan'=>''],
            ], 'evaluation'=>['competency_decision'=>'','decision_explanation'=>'']];
            $second = $base; $second['id']='projek-002'; $second['student_user']='student-2'; $second['student']['full_name']='Muhammad Danish bin Roslan'; $second['project']['project_title']='Sistem Rekod Inventori Bengkel'; $second['sections']=array_fill(1,13,''); $second['meetings']=[];
            session()->put('demo_records', ['projek-001'=>$base, 'projek-002'=>$second]);
        }
        $records = session('demo_records');
        foreach (['pta-001'=>'projek-001','pta-002'=>'projek-002'] as $old=>$new) {
            if (!isset($records[$old])) continue;
            if (!isset($records[$new])) {
                $records[$new]=$records[$old];
                $records[$new]['id']=$new;
            }
            unset($records[$old]);
            session()->put('demo_records',$records);
        }
        return $records;
    }
    public function assigned(array $actor): array
    {
        return array_filter($this->records(), fn ($r) => $r[$actor['role'].'_user'] === $actor['id']);
    }
    public function get(string $id, array $actor): array
    {
        return $this->assigned($actor)[$id] ?? abort(404);
    }
    public function save(string $id, array $actor, array $data): void
    {
        $this->get($id, $actor);
        session()->put("demo_records.$id", $data);
    }
}
