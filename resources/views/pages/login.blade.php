@extends('layouts.app',['page'=>'login','title'=>'Log masuk'])
@section('content')
<main id="main" class="login-layout">
<section class="login-identity" aria-label="SPARTA"><div class="brand"><span class="brand-mark" aria-hidden="true">S</span><div><div class="brand-name">SPARTA</div><div class="brand-sub">Sistem Pengurusan Projek Tahun Akhir</div></div></div><div class="login-title"><h1>SPARTA.</h1><p>Sistem Pengurusan Projek Tahun Akhir untuk pelajar dan penyelia.</p><div class="login-bookline"><span>Projek Tahun Akhir 1</span><span>Semester 3</span></div></div><p class="login-identity-footer">Kolej Vokasional Sungai Petani 1</p></section>
<section class="login-form-side" x-data="{showPassword:false, login:@js(old('login_identifier','')), password:''}"><h2>Log masuk</h2><p class="intro">Akses buku log menggunakan akaun anda.</p>
@if($errors->any())<div class="notice error" role="alert"><x-icon name="alert-circle"/><p>{{ $errors->first() }}</p></div>@endif
<form method="post" action="{{ route('login.store') }}">@csrf
<div class="field"><label for="login-identifier">ID pengguna</label><input id="login-identifier" name="login_identifier" autocomplete="username" x-model="login" value="{{ old('login_identifier') }}" required @if($errors->has('login_identifier')) aria-invalid="true" @endif></div>
<div class="field"><label for="password">Kata laluan</label><div class="password-wrap"><input id="password" name="password" type="password" :type="showPassword?'text':'password'" x-model="password" autocomplete="current-password" required><button class="password-toggle" type="button" @click="showPassword=!showPassword" :aria-pressed="showPassword" aria-label="Tunjukkan kata laluan"><x-icon name="eye"/></button></div></div>
<button type="submit" class="button primary w-full mt-7">Log masuk<x-icon name="arrow-right"/></button></form>
<details class="demo-accounts" open><summary>Akaun pratonton <span aria-hidden="true">+</span></summary><p>Pilih akaun untuk mengisi butiran contoh.</p><div class="demo-account-buttons"><button type="button" @click="login='pelajar@demo.kvsp1.local';password='Demo-PTA1!'">Isi akaun pelajar</button><button type="button" @click="login='penyelia@demo.kvsp1.local';password='Demo-PTA1!'">Isi akaun penyelia</button></div><p>Pelajar: pelajar@demo.kvsp1.local<br>Penyelia: penyelia@demo.kvsp1.local<br>Kata laluan: <strong>Demo-PTA1!</strong></p></details>
<p class="preview-note">Pratonton tempatan dengan data contoh. Jangan masukkan maklumat peribadi sebenar.</p></section></main>
@endsection
