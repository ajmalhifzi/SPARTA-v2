<!DOCTYPE html><html lang="ms"><head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="color-scheme" content="light"><meta name="description" content="Pratonton SPARTA, Sistem Pengurusan Projek Tahun Akhir, Kolej Vokasional Sungai Petani 1.">
<title>{{ $title ?? 'Log masuk' }} | SPARTA</title>@vite(['resources/css/app.css','resources/js/app.js'])
</head><body x-data="{ menuOpen: false }">
<a class="skip-link" href="#main">Langkau ke kandungan</a>
@if(($page ?? '') !== 'login')
<aside id="document-navigation" class="sidebar" :class="{'is-open':menuOpen}" aria-label="Navigasi buku log">
<div class="brand"><span class="brand-mark" aria-hidden="true">S</span><div><div class="brand-name">SPARTA</div><div class="brand-sub">Sistem Pengurusan Projek Tahun Akhir</div></div></div>
<div class="semester-tag"><span>Projek Tahun Akhir</span><span>SPARTA</span></div>
<nav aria-label="Kandungan dokumen">
@if(session('actor.role')==='supervisor')<a class="nav-item {{ $page==='projects'?'active':'' }}" href="{{ route('projects') }}" @if($page==='projects') aria-current="page" @endif><x-icon name="folders"/>Projek seliaan</a>@endif
@isset($project)
@php($profileItem=config('logbook.pages.profile'))
<a class="nav-item {{ $page==='profile'?'active':'' }}" href="{{ route('document',['project'=>$project,'page'=>'profile']) }}" @if($page==='profile') aria-current="page" @endif><x-icon :name="$profileItem[1]"/><span>{{ $profileItem[0] }}</span></a>
<details class="pta-navigation logbook-navigation" open><summary>Buku Log<x-icon name="chevron-down"/></summary><div class="pta-navigation-tabs">
<details class="pta-navigation phase-navigation" @if(($phase??1)===1 && in_array($page,['schedule','meeting','evaluation'])) open @endif><summary>PTA 1<x-icon name="chevron-down"/></summary><div class="pta-navigation-tabs">
@foreach(['schedule','meeting'] as $slug)@php($item=config('logbook.pages.'.$slug))<a class="nav-item {{ ($phase??1)===1 && $page===$slug?'active':'' }}" href="{{ route('pta1.document',['project'=>$project,'page'=>$slug]) }}" @if(($phase??1)===1 && $page===$slug) aria-current="page" @endif><x-icon :name="$item[1]"/><span>{{ $item[0] }}</span></a>@endforeach
@if(session('actor.role')==='supervisor')@php($item=config('logbook.pages.evaluation'))<a class="nav-item {{ $page==='evaluation'?'active':'' }}" href="{{ route('document',['project'=>$project,'page'=>'evaluation']) }}" @if($page==='evaluation') aria-current="page" @endif><x-icon :name="$item[1]"/><span>{{ $item[0] }}</span></a>@endif
</div></details>
<details class="pta-navigation phase-navigation" @if(($phase??1)===2) open @endif><summary>PTA 2<x-icon name="chevron-down"/></summary><div class="pta-navigation-tabs">
@foreach(['schedule','meeting'] as $slug)@php($item=config('logbook.pages.'.$slug))<a class="nav-item {{ ($phase??1)===2 && $page===$slug?'active':'' }}" href="{{ route('pta2.document',['project'=>$project,'page'=>$slug]) }}" @if(($phase??1)===2 && $page===$slug) aria-current="page" @endif><x-icon :name="$item[1]"/><span>{{ $item[0] }}</span></a>@endforeach
</div></details></div></details>
@foreach(['proposal','report','slides'] as $slug)@php($item=config('logbook.pages.'.$slug))<a class="nav-item {{ $page===$slug?'active':'' }}" href="{{ route('document',['project'=>$project,'page'=>$slug]) }}" @if($page===$slug) aria-current="page" @endif><x-icon :name="$item[1]"/><span>{{ $item[0] }}</span></a>@endforeach
@endisset</nav><div class="sidebar-footer">Kolej Vokasional<br>Sungai Petani 1</div></aside>
<div class="workspace"><header class="topbar">
<button class="icon-button mobile-menu" @click="menuOpen=!menuOpen" :aria-expanded="menuOpen" aria-controls="document-navigation" aria-label="Buka atau tutup navigasi"><x-icon name="menu-2"/></button>
<div class="breadcrumb"><span>{{ in_array($page ?? '',['schedule','meeting','evaluation']) ? 'PTA '.($phase??1) : 'SPARTA' }}</span><span class="crumb-detail" aria-hidden="true">/</span><span class="crumb-detail">{{ $title }}</span></div>
<div class="account"><span class="account-initials" aria-hidden="true">{{ session('actor.role')==='student'?'NA':'FH' }}</span><div class="account-name">{{ session('actor.name') }}<div class="account-role">{{ session('actor.role')==='student'?'Pelajar':'Penyelia Projek' }}</div></div><form action="{{ route('logout') }}" method="post">@csrf<button class="icon-button" title="Log keluar" aria-label="Log keluar"><x-icon name="logout"/></button></form></div>
</header><main id="main" class="main"><div class="print-only print-watermark">PRATONTON · DATA CONTOH · BUKAN DOKUMEN RASMI</div>
@if(session('success'))<div class="notice success" role="status"><x-icon name="circle-check"/><p>{{ session('success') }}</p></div>@endif
@if($errors->any())<div class="notice error" role="alert"><x-icon name="alert-circle"/><div><strong>Draf belum disimpan.</strong><p>Semak medan yang ditandakan di bawah.</p><ul>@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
@yield('content')<footer class="page-footer"><span>Pratonton tempatan · Semua rekod ialah data contoh.</span><span>SPARTA · Sistem Pengurusan Projek Tahun Akhir</span></footer></main></div>
@else @yield('content') @endif
</body></html>
