<textarea id="editor" class="form-control" rows="10" name="content">{!! $file->content !!}</textarea>

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
@endsection

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/yaml/yaml.min.js"></script>
<script>
    var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
        mode: "yaml",
        lineNumbers: true,
        indentUnit: 2,
        tabSize: 2,
        lineWrapping: true,
        autoCloseBrackets: true,
        matchBrackets: true,
        extraKeys: {"Ctrl-Space": "autocomplete"}
    });
</script>
@endsection