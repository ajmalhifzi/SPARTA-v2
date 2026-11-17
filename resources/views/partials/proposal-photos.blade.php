@php($photos=$record['attachments'][13]??[])
@php($count=max(2, $photos ? max(array_keys($photos))+1 : 0, count(old('photos',[]))))
<div x-data="{ count: {{ $count }}, previews: {}, preview(event,index) { if(this.previews[index]) URL.revokeObjectURL(this.previews[index]); this.previews[index]=event.target.files[0] ? URL.createObjectURL(event.target.files[0]) : ''; } }">
<p class="field-hint mb-5">Masukkan foto dan keterangan. Pilih bahagian untuk memautkan foto; foto yang dipautkan turut dipaparkan dalam bahagian tersebut.</p>
@for($i=0;$i<$count;$i++)
<section class="form-section" style="padding-bottom:24px">
<h3 class="mb-4">Lampiran {{ $i+1 }}</h3><div class="form-grid">
<div class="field"><label for="photo-{{ $i }}">Foto</label>
@if(!empty($photos[$i]['is_photo']))<img x-show="!previews[{{ $i }}]" src="{{ route('attachment',['project'=>$project,'section'=>13,'index'=>$i,'preview'=>1]) }}" alt="{{ $photos[$i]['caption']??'Lampiran' }}" style="width:100%;max-height:260px;object-fit:contain">@endif
<template x-if="previews[{{ $i }}]"><img :src="previews[{{ $i }}]" alt="Pratonton foto dipilih" style="width:100%;max-height:260px;object-fit:contain"></template>
@unless($readonly)<input id="photo-{{ $i }}" type="file" name="photos[{{ $i }}][image]" accept="image/jpeg,image/png,image/webp" @change="preview($event,{{ $i }})"><p class="field-hint">JPG, PNG atau WebP, sehingga 10 MB. Simpan draf untuk menyimpan foto dan pautan.</p>@endunless
@error('photos.'.$i.'.image')<p class="field-error">{{ $message }}</p>@enderror
</div><div><x-field :name="'photos['.$i.'][caption]'" label="Keterangan foto" type="textarea" :value="$photos[$i]['caption']??''" :readonly="$readonly"/>
<div class="field mt-4"><label for="link-{{ $i }}">Pautkan ke bahagian</label><select id="link-{{ $i }}" name="photos[{{ $i }}][linked_section]" @disabled($readonly)><option value="">Tiada pautan</option>@foreach(config('logbook.sections') as $n=>$label)@if($n!==13)<option value="{{ $n }}" @selected((string)old('photos.'.$i.'.linked_section',$photos[$i]['linked_section']??'')===(string)$n)>{{ $n }}. {{ $label }}</option>@endif @endforeach</select></div></div></div>
</section>
@endfor
@unless($readonly)
<template x-for="i in Math.max(0,count-{{ $count }})" :key="i"><section class="form-section" style="padding-bottom:24px"><h3 class="mb-4" x-text="'Lampiran '+(i+{{ $count }})"></h3><div class="form-grid"><div class="field"><label :for="'new-photo-'+i">Foto</label><template x-if="previews[i+{{ $count }}-1]"><img :src="previews[i+{{ $count }}-1]" alt="Pratonton foto dipilih" style="width:100%;max-height:260px;object-fit:contain"></template><input :id="'new-photo-'+i" type="file" :name="'photos['+(i+{{ $count }}-1)+'][image]'" accept="image/jpeg,image/png,image/webp" @change="preview($event,i+{{ $count }}-1)"></div><div><div class="field"><label :for="'new-caption-'+i">Keterangan foto</label><textarea :id="'new-caption-'+i" :name="'photos['+(i+{{ $count }}-1)+'][caption]'" rows="4"></textarea></div><div class="field mt-4"><label :for="'new-link-'+i">Pautkan ke bahagian</label><select :id="'new-link-'+i" :name="'photos['+(i+{{ $count }}-1)+'][linked_section]'"><option value="">Tiada pautan</option>@foreach(config('logbook.sections') as $n=>$label)@if($n!==13)<option value="{{ $n }}">{{ $n }}. {{ $label }}</option>@endif @endforeach</select></div></div></div></section></template>
<button type="button" class="button mt-4" @click="count++; dirty=true"><x-icon name="plus"/>Tambah</button>
@endunless
@foreach($photos as $i=>$photo)@if(empty($photo['is_photo']))<p class="mt-4"><a href="{{ route('attachment',['project'=>$project,'section'=>13,'index'=>$i]) }}">Lampiran sedia ada: {{ $photo['name'] }}</a></p>@endif @endforeach
</div>
