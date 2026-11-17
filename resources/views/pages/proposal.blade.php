@extends('layouts.app')
@section('content')
@include('partials.heading',['heading'=>'Kertas Cadangan Projek PTA 1','description'=>'Lengkapkan kertas cadangan mengikut 13 perkara dalam borang asal.'])
@include('partials.context')
@php($readonly=session('actor.role')!=='student')
<div class="proposal-grid"><aside class="proposal-index"><h2>Perkara kertas cadangan</h2><nav aria-label="13 perkara kertas cadangan">@foreach(config('logbook.sections') as $n=>$label)<a class="section-link {{ request('tab')!=='acknowledgement' && $section===$n?'active':'' }}" href="{{ route('document',['project'=>$project,'page'=>$page,'section'=>$n]) }}" @if(request('tab')!=='acknowledgement' && $section===$n) aria-current="page" @endif><span class="section-no">{{ str_pad($n,2,'0',STR_PAD_LEFT) }}</span><span>{{ $label }}</span>@if(trim($record['sections'][$n])!=='')<x-icon name="check" aria-label="Mengandungi draf"/>@endif</a>@endforeach<a class="section-link {{ request('tab')==='acknowledgement'?'active':'' }}" href="{{ route('document',['project'=>$project,'page'=>$page,'tab'=>'acknowledgement']) }}">Perakuan Pengesahan</a></nav><p class="index-note">Sila gunakan helaian tambahan jika ruangan tidak mencukupi.</p></aside>
<div class="min-w-0">
@if(request('tab')==='acknowledgement')
<section class="document"><div class="document-bar"><h2>Perakuan Pengesahan</h2></div><div class="document-body">@include('partials.proposal-signatures')</div></section>
@else
<form method="post" enctype="multipart/form-data" action="{{ route('document.save',['project'=>$project,'page'=>$page]) }}" x-data="draftForm" @input="dirty=true" @submit="submit()">@csrf<input type="hidden" name="section" value="{{ $section }}">
<div class="document screen-editor"><div class="document-bar"><div class="editor-title"><span class="editor-number">{{ str_pad($section,2,'0',STR_PAD_LEFT) }}</span><h2>{{ config('logbook.sections')[$section] }}</h2></div><span class="badge">{{ $readonly?'Baca sahaja':'Draf' }}</span></div><div class="document-body editor">
@if($section===13)
@include('partials.proposal-photos')
@else
@foreach($record['attachments'][13]??[] as $photoIndex=>$photo)
@if(($photo['linked_section']??null)===$section)
<figure class="mb-5"><img style="max-width:100%;max-height:320px;object-fit:contain" src="{{ route('attachment',['project'=>$project,'section'=>13,'index'=>$photoIndex,'preview'=>1]) }}" alt="{{ $photo['caption']??'Lampiran' }}"><figcaption>Lampiran {{ $photoIndex+1 }}: {{ $photo['caption']??'' }}</figcaption></figure>
@endif
@endforeach
@if($section===10)<p class="field-hint mb-5">Rujuk Lampiran.</p>@endif
<x-field name="content" label="Ringkasan" type="textarea" :rows="10" :value="$record['sections'][$section]" :readonly="$readonly"/>
<div class="editor-foot"><span>Sila gunakan helaian tambahan jika ruangan tidak mencukupi.</span><span>{{ $section }} / 13</span></div>
<div class="field mt-6"><label for="attachment">{{ $section===10 ? 'Carta Gantt / lampiran' : 'Lampiran' }}</label>@unless($readonly)<input id="attachment" name="attachment" type="file" accept=".pdf,.png,.jpg,.jpeg" aria-describedby="attachment-help"><p class="field-hint" id="attachment-help">PDF, PNG atau JPG, sehingga 10 MB. Simpan draf untuk memuat naik.</p>@endunless</div>
@error('attachment')<p class="field-error">{{ $message }}</p>@enderror
@foreach($record['attachments'][$section]??[] as $index=>$file)<a class="button text mt-3" href="{{ route('attachment',['project'=>$project,'section'=>$section,'index'=>$index]) }}"><x-icon name="paperclip"/>{{ $file['name'] }}</a>@endforeach
@endif
</div>@unless($readonly)<x-save-bar/>@endunless</div>
<div class="print-only"><h2>Maklumat Pelajar</h2>@include('partials.project-fields')<hr class="mt-5 mb-5">@foreach(config('logbook.sections') as $n=>$label)<section class="print-section"><h3>{{ $n }}. {{ $label }}</h3>@if($n===10)<p>Rujuk Lampiran</p>@endif<p>{{ $record['sections'][$n] ?: 'Belum diisi' }}</p>@foreach($record['attachments'][$n]??[] as $file)<p>Lampiran: {{ $file['name'] }}</p>@endforeach</section>@endforeach</div>

</form>

<div class="flex justify-between gap-4 mt-6 no-print">@if($section>1)<a class="button text" href="{{ route('document',['project'=>$project,'page'=>$page,'section'=>$section-1]) }}"><x-icon name="arrow-left"/>Perkara sebelumnya</a>@else<span></span>@endif @if($section<13)<a class="button text" href="{{ route('document',['project'=>$project,'page'=>$page,'section'=>$section+1]) }}">Perkara seterusnya<x-icon name="arrow-right"/></a>@endif</div>
@endif
</div></div>
@endsection
