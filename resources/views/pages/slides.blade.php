@extends('layouts.app')
@section('content')
@include('partials.heading',['description'=>'Muat naik slaid pembentangan atau simpan pautan Canva mengikut fasa projek.'])
@include('partials.context')
@foreach([1,2] as $phase)
@php($items=$record['slides'][$phase]??[])
<section class="document slide-phase" id="pta-{{ $phase }}" aria-labelledby="slide-phase-{{ $phase }}">
    <div class="document-bar"><h2 id="slide-phase-{{ $phase }}">PTA {{ $phase }}</h2><span class="badge">{{ count($items) }} slaid</span></div>
    <div class="document-body">
        @if($items)
        <div class="slide-list">
            @foreach($items as $id=>$slide)
            <article class="slide-entry">
                <div class="slide-entry-head"><div><span class="slide-sequence">{{ str_pad($loop->iteration,2,'0',STR_PAD_LEFT) }}</span><h3>{{ ['pptx'=>'PowerPoint','pdf'=>'PDF','images'=>'Halaman gambar','canva'=>'Canva'][$slide['format']]??'Slaid' }}</h3></div><span class="badge">{{ $slide['format']==='images' ? count($slide['pages']).' halaman' : strtoupper($slide['format']) }}</span></div>
                @if($slide['format']==='canva')
                    <a class="slide-link" href="{{ $slide['url'] }}" target="_blank" rel="noopener noreferrer">Buka pautan Canva <x-icon name="arrow-up-right"/></a>
                @elseif($slide['format']==='images')
                    <ol class="slide-pages">
                        @foreach($slide['pages'] as $index=>$file)
                        <li class="slide-page"><img src="{{ route('slides.file',['project'=>$project,'item'=>$id,'index'=>$index,'preview'=>1]) }}" alt="Halaman slaid {{ $index+1 }}"><div class="slide-page-meta"><span><strong>{{ str_pad($index+1,2,'0',STR_PAD_LEFT) }}</strong> {{ $file['name'] }}</span><div class="slide-page-actions"><a class="button text" href="{{ route('slides.file',['project'=>$project,'item'=>$id,'index'=>$index]) }}">Muat turun</a>@if(session('actor.role')==='student')@if($index>0)<form method="post" action="{{ route('slides.pages.move',['project'=>$project,'item'=>$id,'index'=>$index]) }}">@csrf<input type="hidden" name="direction" value="up"><button class="slide-move" type="submit" aria-label="Alih halaman {{ $index+1 }} ke atas">↑</button></form>@endif @if($index<count($slide['pages'])-1)<form method="post" action="{{ route('slides.pages.move',['project'=>$project,'item'=>$id,'index'=>$index]) }}">@csrf<input type="hidden" name="direction" value="down"><button class="slide-move" type="submit" aria-label="Alih halaman {{ $index+1 }} ke bawah">↓</button></form>@endif @endif</div></div></li>
                        @endforeach
                    </ol>
                    @if(session('actor.role')==='student')<form class="slide-add-pages no-print" method="post" enctype="multipart/form-data" action="{{ route('slides.pages.store',['project'=>$project,'item'=>$id]) }}">@csrf<div class="field"><label for="more-images-{{ $id }}">Tambah halaman gambar</label><input id="more-images-{{ $id }}" type="file" name="images[]" accept=".jpg,.jpeg,.png" multiple required><p class="field-hint">JPG, JPEG atau PNG. Anda boleh memilih beberapa gambar sekali gus.</p></div><button class="button" type="submit"><x-icon name="plus"/>Tambah halaman</button></form>@endif
                @else
                    <a class="slide-link" href="{{ route('slides.file',['project'=>$project,'item'=>$id,'index'=>0]) }}"><x-icon name="paperclip"/>{{ $slide['pages'][0]['name'] }}</a>
                @endif
            </article>
            @endforeach
        </div>
        @else
        <div class="slide-empty">Belum ada slaid untuk PTA {{ $phase }}.</div>
        @endif
        @if(session('actor.role')==='student')
        <form class="slide-upload no-print" method="post" enctype="multipart/form-data" action="{{ route('slides.store',['project'=>$project]) }}" x-data="{format: @js((string)old('phase')===(string)$phase ? old('format','') : '')}">@csrf
            <input type="hidden" name="phase" value="{{ $phase }}">
            <h3>Tambah slaid</h3>
            <div class="field"><label for="format-{{ $phase }}">Pilih format dahulu</label><select id="format-{{ $phase }}" name="format" x-model="format" required><option value="">Pilih format</option><option value="pptx">PowerPoint (.pptx)</option><option value="pdf">PDF (.pdf)</option><option value="images">Gambar (.jpg, .jpeg, .png)</option><option value="canva">Pautan Canva</option></select></div>
            <template x-if="format==='pptx'"><div class="field mt-4"><label for="pptx-{{ $phase }}">Fail PowerPoint</label><input id="pptx-{{ $phase }}" type="file" name="slide_file" accept=".pptx" required></div></template>
            <template x-if="format==='pdf'"><div class="field mt-4"><label for="pdf-{{ $phase }}">Fail PDF</label><input id="pdf-{{ $phase }}" type="file" name="slide_file" accept=".pdf" required></div></template>
            <template x-if="format==='images'"><div class="field mt-4"><label for="images-{{ $phase }}">Halaman gambar</label><input id="images-{{ $phase }}" type="file" name="images[]" accept=".jpg,.jpeg,.png" multiple required><p class="field-hint">Pilih beberapa gambar, kemudian susun halaman selepas menyimpan.</p></div></template>
            <template x-if="format==='canva'"><div class="field mt-4"><label for="canva-{{ $phase }}">Pautan Canva</label><input id="canva-{{ $phase }}" type="url" name="canva_url" placeholder="https://www.canva.com/design/..." value="{{ (string)old('phase')===(string)$phase ? old('canva_url') : '' }}" required></div></template>
            <p class="field-hint mt-4">Saiz maksimum 25 MB bagi setiap fail. Anda boleh menambah slaid lagi pada bila-bila masa.</p>
            <button class="button primary mt-4" type="submit">Simpan slaid<x-icon name="arrow-right"/></button>
        </form>
        @endif
    </div>
</section>
@endforeach
@endsection
