<div class="form-group ">
    <label class="control-label">{{ $name }}</label>
    <span class="hint">{{ @$field->hint }}</span>
    <input type="{{ $type }}" class="form-control" name="text|{{ $name }}" placeholder="{{ $name }}" {{ @$field->required }}>
</div>
