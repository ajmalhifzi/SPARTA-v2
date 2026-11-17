@php($readonly = true)
<div class="form-grid">
<x-field name="project[programme_name]" label="Nama Program" :value="$record['project']['programme_name']" :readonly="$readonly"/>
@if($page==='proposal')<x-field name="project[course_code]" label="Kod Kursus" :value="$record['project']['course_code']" :readonly="$readonly"/>
@else<x-field name="project[programme_code]" label="Kod Program" :value="$record['project']['programme_code']" :readonly="$readonly"/>@endif
<x-field class="span-full" name="project[project_title]" label="Tajuk Projek" :value="$record['project']['project_title']" :readonly="$readonly"/>
<x-field name="display_student_name" label="Nama Pelajar (Dengan Huruf Besar)" :value="mb_strtoupper($record['student']['full_name'])" :readonly="true" hint="Daripada Maklumat Diri Pelajar."/>
<x-field name="display_identity" label="No. Kad Pengenalan" :value="$record['student']['identity_number']" :readonly="true"/>
<x-field class="span-full" name="display_supervisor" label="Nama Penyelia Projek" :value="$record['supervisor_name']" :readonly="true"/>
</div>
