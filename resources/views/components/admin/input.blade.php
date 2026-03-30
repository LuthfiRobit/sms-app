@props(['name', 'label' => false, 'type' => 'text', 'placeholder' => '', 'value' => '', 'required' => false])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">{{ $label }} @if($required) <span class="text-danger">*</span> @endif</label>
    @endif
    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" class="form-control @error($name) is-invalid @enderror" placeholder="{{ $placeholder }}" value="{{ old($name, $value) }}" {{ $required ? 'required' : '' }} {{ $attributes }}>
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
