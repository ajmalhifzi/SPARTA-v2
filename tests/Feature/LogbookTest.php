<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
class LogbookTest extends TestCase
{
    protected function setUp(): void { parent::setUp(); config(['logbook.demo'=>true]); $this->withoutVite(); }
    private function student(): array { return ['actor'=>['id'=>'student-1','role'=>'student','name'=>'Nur Aisyah']]; }
    private function supervisor(): array { return ['actor'=>['id'=>'supervisor-1','role'=>'supervisor','name'=>'Faridah']]; }
    public function test_remaining_pages_render_for_their_roles(): void
    {
        $this->get('/')->assertOk()->assertSee('Log masuk');
        foreach (array_diff(array_keys(config('logbook.pages')),['evaluation','schedule','meeting']) as $page) $this->withSession($this->student())->get('/projek/projek-001/'.$page)->assertOk();
        foreach (['schedule','meeting'] as $page) $this->get('/projek/projek-001/buku-log/pta1/'.$page)->assertOk();
        $this->withSession($this->supervisor())->get('/projek')->assertOk()->assertSee('Muhammad Danish');
        $this->withSession($this->supervisor())->get('/projek/projek-001/evaluation')->assertOk()->assertSee('60')->assertSee('Perlu audit sumber');
    }
    public function test_demo_authentication_and_role_routing(): void
    {
        $this->post('/login',['login_identifier'=>'bad','password'=>'bad'])->assertSessionHasErrors('login_identifier');
        $this->post('/login',['login_identifier'=>'pelajar@demo.kvsp1.local','password'=>'Demo-PTA1!'])->assertRedirect('/projek/projek-001/profile')->assertSessionHas('actor.role','student');
        $this->post('/logout')->assertRedirect('/')->assertSessionMissing('actor');
        $this->post('/login',['login_identifier'=>'penyelia@demo.kvsp1.local','password'=>'Demo-PTA1!'])->assertRedirect('/projek');
    }
    public function test_authorization_is_enforced_on_the_server(): void
    {
        $this->get('/projek/projek-001/profile')->assertRedirect('/');
        $this->withSession($this->student())->get('/projek/projek-001/cover')->assertNotFound();
        $this->get('/projek/projek-001/logbook-title')->assertNotFound();
        $this->get('/projek/projek-001/pta-info')->assertNotFound();
        $this->post('/projek/projek-001/pta-info',[])->assertNotFound();
        $this->withSession($this->student())->get('/projek')->assertForbidden();
        $this->get('/projek/projek-002/profile')->assertNotFound();
        $this->post('/projek/projek-002/profile',['student'=>['full_name'=>'Changed']])->assertNotFound();
        $this->post('/projek/projek-001/evaluation',['competency_decision'=>'Terampil'])->assertForbidden();
        $this->get('/projek/projek-001/evaluation')->assertForbidden();
        foreach(['profile','proposal','schedule','meeting'] as $page) $this->withSession($this->supervisor())->post('/projek/projek-001/'.$page,[])->assertForbidden();
    }
    public function test_proposal_persists_and_preserves_code_distinction(): void
    {
        $this->withSession($this->student())->post('/projek/projek-001/proposal',['section'=>12,'content'=>'Rujukan contoh','project'=>['course_code'=>'COURSE-X']])->assertRedirect('/projek/projek-001/proposal?section=12');
        $this->get('/projek/projek-001/proposal?section=12')->assertSee('Rujukan contoh');
        $this->post('/projek/projek-001/profile',['project'=>['programme_code'=>'PROGRAM-Y']])->assertRedirect();
        $this->assertSame('COURSE-X',session('demo_records.projek-001.project.course_code'));
        $this->assertSame('PROGRAM-Y',session('demo_records.projek-001.project.programme_code'));
        $this->assertCount(13,session('demo_records.projek-001.sections'));
        $this->get('/projek/projek-001/proposal?section=14')->assertNotFound();
    }
    public function test_profile_errors_and_two_contacts(): void
    {
        $this->withSession($this->student())->post('/projek/projek-001/profile',['student'=>['college_email'=>'not-email']])->assertSessionHasErrors('student.college_email');
        $this->post('/projek/projek-001/profile',['student'=>['full_name'=>'Nama dikemas kini'],'contacts'=>[1=>['name'=>'Waris pertama'],2=>['name'=>'Waris kedua']]])->assertRedirect();
        $this->assertSame('Nama dikemas kini',session('demo_records.projek-001.student.full_name'));
        $this->assertCount(2,session('demo_records.projek-001.contacts'));
    }
    public function test_meetings_are_shared_between_schedule_and_detail_without_seven_limit(): void
    {
        $this->withSession($this->student());
        for ($i=0;$i<6;$i++) $this->post('/projek/projek-001/buku-log/pta1/schedule',['activity'=>'Sesi tambahan'])->assertRedirect();
        $this->assertCount(8,session('demo_records.projek-001.meetings'));
        $this->post('/projek/projek-001/buku-log/pta1/meeting',['meeting'=>8,'meeting_date'=>'2026-11-03','day_label'=>'Selasa','meeting_time'=>'14:30','activity'=>'Aktiviti dikemas kini','discussion_summary'=>'Catatan contoh'])->assertRedirect();
        $this->get('/projek/projek-001/buku-log/pta1/schedule')->assertSee('Aktiviti dikemas kini');
        $this->get('/projek/projek-001/buku-log/pta1/meeting?meeting=8')->assertSee('Catatan contoh')->assertSee('Selasa');
        $this->post('/projek/projek-001/buku-log/pta1/meeting',['meeting'=>99])->assertNotFound();
        $this->post('/projek/projek-001/buku-log/pta1/schedule',['meeting_date'=>'invalid'])->assertSessionHasErrors('meeting_date');
    }
    public function test_evaluation_is_private_and_only_draft_fields_can_be_saved(): void
    {
        $this->withSession($this->supervisor())->post('/projek/projek-001/evaluation',['competency_decision'=>'Terampil','decision_explanation'=>'Ulasan sulit contoh','final_mark'=>100])->assertRedirect();
        $this->assertArrayNotHasKey('final_mark',session('demo_records.projek-001.evaluation'));
        $this->withSession($this->student())->get('/projek/projek-001/evaluation')->assertForbidden();
        $this->withSession($this->supervisor())->post('/projek/projek-001/evaluation',['competency_decision'=>'Lulus'])->assertSessionHasErrors('competency_decision');
    }
    public function test_project_search_and_empty_state(): void
    {
        $this->withSession($this->supervisor())->get('/projek?q=Danish')->assertSee('Muhammad Danish')->assertDontSee('Nur Aisyah');
        $this->get('/projek?q=missing-name')->assertSee('Tiada projek ditemui');
        $this->get('/projek/projek-002/buku-log/pta1/meeting')->assertSee('Tiada rekod perjumpaan');
    }
    public function test_pta2_meetings_are_separate_and_sidebar_has_requested_order(): void
    {
        $this->withSession($this->student());
        $this->get('/projek/projek-001/buku-log/pta2/schedule')->assertOk()->assertSee('Jadual Perjumpaan PTA 2')->assertSee('Belum ada perjumpaan');
        $this->post('/projek/projek-001/buku-log/pta2/schedule',['activity'=>'Perjumpaan PTA 2'])->assertRedirect('/projek/projek-001/buku-log/pta2/meeting?meeting=1');
        $this->post('/projek/projek-001/buku-log/pta2/meeting',['meeting'=>1,'discussion_summary'=>'Kemajuan semester seterusnya'])->assertRedirect();
        $this->get('/projek/projek-001/buku-log/pta2/meeting?meeting=1')->assertSee('Kemajuan semester seterusnya');
        $this->get('/projek/projek-001/buku-log/pta1/schedule')->assertDontSee('Perjumpaan PTA 2');
        $this->get('/projek/projek-001/buku-log/pta1/meeting?meeting=1')->assertDontSee('Kemajuan semester seterusnya');
        $this->assertCount(2,session('demo_records.projek-001.meetings'));
        $this->assertCount(1,session('demo_records.projek-001.meetings_pta2'));
        $this->get('/projek/projek-001/buku-log/pta2/evaluation')->assertNotFound();
        $this->get('/projek/projek-001/report')->assertOk()->assertSee('Belum ada kandungan');
        $this->get('/projek/projek-001/slides')->assertOk()->assertSee('Belum ada slaid untuk PTA 1.');
        $html=$this->get('/projek/projek-001/profile')->getContent();
        $this->assertTrue(strpos($html,'>Maklumat Pelajar</span>') < strpos($html,'<summary>Buku Log'));
        $this->assertTrue(strpos($html,'<summary>Buku Log') < strpos($html,'>Kertas Cadangan</span>'));
        $this->assertTrue(strpos($html,'>Kertas Cadangan</span>') < strpos($html,'>Laporan</span>'));
        $this->assertTrue(strpos($html,'>Laporan</span>') < strpos($html,'>Slaid</span>'));
        $this->assertStringNotContainsString('Borang Penilaian PTA 1</span>', $html);
    }
    public function test_old_project_urls_and_drafts_move_to_neutral_project_id(): void
    {
        $this->withSession($this->student())->get('/projek/projek-001/profile')->assertOk();
        $record=session('demo_records.projek-001');
        $record['id']='pta-001';
        $record['meetings_pta2']=[1=>['meeting_number'=>1,'meeting_date'=>'','activity'=>'Draf PTA 2 lama','day_label'=>'','meeting_time'=>'','supervisor_name'=>'','department_programme'=>'','discussion_summary'=>'','next_action_plan'=>'']];
        session()->put('demo_records',['pta-001'=>$record]);
        $this->get('/projek/pta-001/pta2/schedule')->assertRedirect('/projek/projek-001/buku-log/pta2/schedule');
        $this->get('/projek/projek-001/buku-log/pta2/schedule')->assertSee('Draf PTA 2 lama');
        $this->assertSame('projek-001',session('demo_records.projek-001.id'));
        $this->assertNull(session('demo_records.pta-001'));
        $this->get('/projek/projek-001/schedule')->assertRedirect('/projek/projek-001/buku-log/pta1/schedule');
    }
    public function test_private_attachment_download_checks_assignment(): void
    {
        Storage::fake('local');
        $file=UploadedFile::fake()->createWithContent('lampiran.pdf',"%PDF-1.4\n%test");
        $this->withSession($this->student())->post('/projek/projek-001/proposal',['section'=>13,'content'=>'','attachment'=>$file])->assertRedirect();
        $this->get('/projek/projek-001/lampiran/13/0')->assertDownload('lampiran.pdf');
        $this->get('/projek/projek-002/lampiran/13/0')->assertNotFound();
        $this->get('/projek/projek-001/lampiran/13/99')->assertNotFound();
    }
    public function test_profile_fills_repeated_project_fields_and_print_values(): void
    {
        $this->withSession($this->student())->post('/projek/projek-001/profile',[
            'student'=>['full_name'=>'Nama Ujian','identity_number'=>'001122','programme'=>'Program Ujian','address'=>"Alamat panjang\nBaris kedua"],
            'project'=>['programme_code'=>'PRG-01','project_title'=>'Tajuk daripada profil'],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Program Ujian',session('demo_records.projek-001.project.programme_name'));
        $this->get('/projek/projek-001/profile')->assertSee('Program Ujian')->assertSee('PRG-01')->assertSee('Tajuk daripada profil')->assertSee('print-field-value');
        $this->get('/projek/projek-001/proposal')->assertSee('Tajuk daripada profil')->assertSee('Program Ujian');
        $this->get('/projek/projek-001/profile')->assertSee('Baris kedua')->assertSee('PRG-01');
    }
    public function test_preview_is_disabled_without_explicit_flag(): void
    {
        config(['logbook.demo'=>false]); $this->get('/')->assertStatus(503);
    }
    public function test_proposal_tabs_and_linked_photos(): void
    {
        Storage::fake('local');
        $this->withSession($this->student())->get('/projek/projek-001/proposal')->assertOk()->assertDontSee('<summary>Maklumat Pelajar', false)->assertDontSee('Nama Pegawai');
        $this->get('/projek/projek-001/proposal?tab=acknowledgement')->assertOk()->assertSee('Nama Pegawai');
        $this->get('/projek/projek-001/proposal?section=13')->assertOk()->assertSee('Lampiran 2')->assertSee('Tambah')->assertDontSee('name="content"',false);
        $file=UploadedFile::fake()->createWithContent('foto.png',base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6ksAAAAAASUVORK5CYII='));
        $this->post('/projek/projek-001/proposal',['section'=>13,'photos'=>[0=>['image'=>$file,'caption'=>'Rajah metodologi','linked_section'=>7]]])->assertSessionHasNoErrors()->assertRedirect();
        $this->get('/projek/projek-001/proposal?section=7')->assertSee('Rajah metodologi');
        $this->get('/projek/projek-001/lampiran/13/0?preview=1')->assertOk();
        $this->post('/projek/projek-001/proposal',['section'=>13,'photos'=>[0=>['caption'=>'Rajah kos','linked_section'=>8]]])->assertSessionHasNoErrors();
        $this->get('/projek/projek-001/proposal?section=8')->assertSee('Rajah kos');
        $this->get('/projek/projek-001/proposal?section=7')->assertDontSee('Rajah kos');
        $this->post('/projek/projek-001/proposal',['section'=>13,'photos'=>[0=>['linked_section'=>14]]])->assertSessionHasErrors('photos.0.linked_section');
    }
    public function test_slides_support_phase_separation_canva_images_order_and_access(): void
    {
        Storage::fake('local');
        $png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl6ksAAAAAASUVORK5CYII=');
        $this->withSession($this->student())->get('/projek/projek-001/slides')->assertOk()->assertSee('Pilih format dahulu')->assertSee('PTA 1')->assertSee('PTA 2');
        $this->post('/projek/projek-001/slides',['phase'=>1,'format'=>'images','images'=>[
            UploadedFile::fake()->createWithContent('pertama.png',$png),
            UploadedFile::fake()->createWithContent('kedua.png',$png),
        ]])->assertSessionHasNoErrors()->assertRedirect('/projek/projek-001/slides#pta-1');
        $slides=session('demo_records.projek-001.slides.1');
        $id=array_key_first($slides);
        $this->assertSame('pertama.png',$slides[$id]['pages'][0]['name']);
        $this->post("/projek/projek-001/slides/$id/pages/1/move",['direction'=>'up'])->assertRedirect();
        $this->assertSame('kedua.png',session("demo_records.projek-001.slides.1.$id.pages.0.name"));
        $this->post("/projek/projek-001/slides/$id/pages",['images'=>[UploadedFile::fake()->createWithContent('ketiga.png',$png)]])->assertSessionHasNoErrors();
        $this->assertCount(3,session("demo_records.projek-001.slides.1.$id.pages"));
        $this->get("/projek/projek-001/slides/$id/files/0?preview=1")->assertOk();
        $this->post('/projek/projek-001/slides',['phase'=>2,'format'=>'canva','canva_url'=>'https://www.canva.com/design/example'])->assertSessionHasNoErrors();
        $this->assertCount(1,session('demo_records.projek-001.slides.2'));
        $this->post('/projek/projek-001/slides',['phase'=>1,'format'=>'pdf','slide_file'=>UploadedFile::fake()->createWithContent('pembentangan.pdf',"%PDF-1.4\n%demo")])->assertSessionHasNoErrors();
        $this->post('/projek/projek-001/slides',['phase'=>1,'format'=>'pptx','slide_file'=>UploadedFile::fake()->create('pembentangan.pptx',1,'application/vnd.openxmlformats-officedocument.presentationml.presentation')])->assertSessionHasNoErrors();
        $this->post('/projek/projek-001/slides',['phase'=>2,'format'=>'canva','canva_url'=>'https://other.example/design'])->assertSessionHasErrors('canva_url');
        $this->post('/projek/projek-001/slides',['phase'=>1,'format'=>'pdf','slide_file'=>UploadedFile::fake()->create('wrong.txt',1,'text/plain')])->assertSessionHasErrors('slide_file');
        $this->withSession($this->supervisor())->get('/projek/projek-001/slides')->assertSee('kedua.png')->assertDontSee('Pilih format dahulu');
        $this->post('/projek/projek-001/slides',['phase'=>1,'format'=>'canva','canva_url'=>'https://www.canva.com/design/no'])->assertForbidden();
        $this->get("/projek/projek-002/slides/$id/files/0")->assertNotFound();
    }
}
