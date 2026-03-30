@props(['name', 'label' => false, 'options' => [], 'selected' => '', 'required' => false])

<div class="mb-3">
    @if($label)
        <label for="{{ $name }}" class="form-label">{{ $label }} @if($required) <span class="text-danger">*</span> @endif</label>
    @endif
    <select name="{{ $name }}" id="{{ $name }}" class="form-select @error($name) is-invalid @enderror" {{ $required ? 'required' : '' }} {{ $attributes }}>
        <option value="">-- Pilih --</option>
        @if(is_array($options) || is_object($options))
            @foreach($options as $value => $text)
                <option value="{{ $value }}" {{ old($name, $selected) == $value ? 'selected' : '' }}>
                    {{ $text }}
                </option>
            @endforeach
        @endif
        {{ $slot }}
    </select>
    @error($name)
        <div class="invalid-feedback">
            {{ $message }}
        </div>
    @enderror
</div>
