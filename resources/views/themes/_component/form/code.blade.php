<div class="form-group">
    <label>{{ __($fieldLang) }}</label>
    <pre>{{ json_encode(json_decode($fieldValue, true), JSON_PRETTY_PRINT) }}</pre>
</div>
