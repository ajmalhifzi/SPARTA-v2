@extends('layouts.app')
@section('content')
@include('partials.heading',['description'=>'Maklumat peribadi, kolej dan dua orang untuk dihubungi ketika kecemasan.'])
@php($readonly = session('actor.role')!=='student')
@if($readonly)<div class="notice"><x-icon name="lock"/><p>Paparan penyelia. Maklumat ini dikemas kini oleh pelajar.</p></div>@endif
<form class="document" method="post" action="{{ route('document.save',['project'=>$project,'page'=>$page]) }}" x-data="draftForm" @input="dirty=true" @submit="submit()">@csrf
<div class="document-bar"><h2>Maklumat Diri Pelajar</h2><span class="badge">{{ $readonly?'Baca sahaja':'Draf pelajar' }}</span></div><div class="document-body">
<section class="form-section"><div class="section-title"><h2>Butiran pelajar</h2></div><div class="form-grid">
@foreach(['full_name'=>'Nama','identity_number'=>'No. Kad Pengenalan','year_semester'=>'Tahun / Semester','programme'=>'Nama Program','telephone'=>'No. Telefon'] as $key=>$label)<x-field :name="'student['.$key.']'" :label="$label" :value="$record['student'][$key]" :readonly="$readonly" :type="$key==='telephone'?'tel':'text'"/>@endforeach
<x-field name="display_supervisor" label="Nama Penyelia Projek" :value="$record['supervisor_name']" :readonly="true" hint="Mengikut penyelia projek yang ditetapkan."/>
<x-field class="span-full" name="student[address]" label="Alamat" type="textarea" :rows="3" :value="$record['student']['address']" :readonly="$readonly"/></div></section>
<section class="form-section"><div class="section-title"><h2>Maklumat projek PTA 1</h2><p>Nama Program menggunakan maklumat program pelajar di atas. Butiran ini diisi secara automatik pada halaman berkaitan.</p></div><div class="form-grid"><x-field name="project[programme_code]" label="Kod Program" :value="$record['project']['programme_code']" :readonly="$readonly"/><x-field name="project[project_title]" label="Tajuk Projek" :value="$record['project']['project_title']" :readonly="$readonly"/></div></section>
<section class="form-section"><div class="section-title"><h2>Maklumat kolej</h2></div><div class="form-grid">
<x-field class="span-full" name="student[college_name]" label="Nama Kolej Vokasional" :value="$record['student']['college_name']" :readonly="$readonly"/>
<x-field class="span-full" name="student[college_address]" label="Alamat Kolej" type="textarea" :rows="3" :value="$record['student']['college_address']" :readonly="$readonly"/>
<x-field name="student[college_telephone]" label="No. Telefon Kolej" type="tel" :value="$record['student']['college_telephone']" :readonly="$readonly"/>
<x-field name="student[college_email]" label="Emel Kolej" type="email" :value="$record['student']['college_email']" :readonly="$readonly"/>
</div></section>
<section class="form-section"><div class="section-title"><h2>Maklumat waris / hubungan kecemasan</h2><p>Dua set maklumat hubungan seperti pada borang bercetak.</p></div>
@foreach([1,2] as $n)<fieldset class="form-section"><legend class="font-medium text-sm mb-4">Hubungan {{ $n }}</legend><div class="form-grid three">@foreach(['name'=>'Nama','telephone'=>'No. Telefon','relationship'=>'Pertalian'] as $key=>$label)<x-field :name="'contacts['.$n.']['.$key.']'" :label="$label" :value="$record['contacts'][$n][$key]" :readonly="$readonly" :type="$key==='telephone'?'tel':'text'"/>@endforeach</div></fieldset>@endforeach
</section></div>@unless($readonly)<x-save-bar/>@endunless</form>
@endsection
