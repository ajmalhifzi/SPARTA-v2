<div class="page-heading"><div><h1>{{ $heading ?? $title }}</h1><p>{{ $description }}</p></div><div class="heading-actions no-print">
@if(($page ?? '') === 'meeting' && session('actor.role') === 'student')
<form method="post" action="{{ route(($phase??1)===2?'pta2.document.save':'pta1.document.save',['project'=>$project,'page'=>'schedule']) }}">@csrf<button class="button primary" type="submit"><x-icon name="plus"/>Tambah Rekod</button></form>
@endif
<button class="button" type="button" @click="window.print()"><x-icon name="printer"/>Cetak</button></div></div>
