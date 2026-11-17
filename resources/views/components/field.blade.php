@props(['name','label','value'=>'','type'=>'text','readonly'=>false,'hint'=>null,'rows'=>4])
@php($key = trim(str_replace(['][','[',']'],['.','.',''],$name),'.'))
@php($id = 'field-'.str_replace('.','-',$key))
<div {{ $attributes->class(['field']) }}>
    <label for="{{ $id }}">{{ $label }}</label>
    @if($type==='textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" rows="{{ $rows }}" @readonly($readonly) @if($errors->has($key)) aria-invalid="true" @endif aria-describedby="{{ $id }}-help">{{ old($key,$value) }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" value="{{ old($key,$value) }}" @readonly($readonly) @if($errors->has($key)) aria-invalid="true" @endif aria-describedby="{{ $id }}-help">
    @endif
    <div class="print-field-value" style="display:none">{{ old($key,$value) }}</div>
    <div id="{{ $id }}-help">@if($hint)<p class="field-hint">{{ $hint }}</p>@endif @error($key)<p class="field-error">{{ $message }}</p>@enderror</div>
</div>
