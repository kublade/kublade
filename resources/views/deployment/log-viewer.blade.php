<textarea id="editor" class="form-control" rows="10" name="content">{!! $log->logs !!}</textarea>
<div class="small mb-4">
  {{ __('Last sync') }}: {{ $log->updated_at?->format('Y-m-d H:i:s') ?? $log->created_at?->format('Y-m-d H:i:s') ?? __('N/A') }}
</div>

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<style>
  .CodeMirror {
    height: auto;
    border: 1px solid #ccc;
    font-size: 14px;
    border-radius: 0.5rem;
    margin: 1.5rem 0;
  }

  .cm-log-level-error {
    color: #d73a49;
    font-weight: bold;
  }

  .cm-log-level-warning {
    color: #e36209;
  }

  .cm-log-level-info {
    color: #0366d6;
  }

  .cm-log-level-debug {
    color: #22863a;
  }

  .cm-timestamp {
    color: #6a737d;
  }
</style>
@endsection

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/javascript/javascript.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/mode/simple.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
<script>
  CodeMirror.defineSimpleMode("log", {
    start: [
      { regex: /(\d{4}-\d{2}-\d{2}\s\d{2}:\d{2}:\d{2})/, token: "timestamp" },
      { regex: /(ERROR|CRITICAL|FATAL)/, token: "log-level-error" },
      { regex: /(WARNING|WARN)/, token: "log-level-warning" },
      { regex: /(INFO)/, token: "log-level-info" },
      { regex: /(DEBUG|TRACE)/, token: "log-level-debug" },
      { regex: /".*?"/, token: "string" },
      { regex: /'.*?'/, token: "string" },
      { regex: /[0-9]+/, token: "number" },
      { regex: /[a-zA-Z_]\w*/, token: "variable" }
    ]
  });

  var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
    mode: 'log',
    lineNumbers: true,
    indentUnit: 2,
    tabSize: 2,
    lineWrapping: true,
    autoCloseBrackets: true,
    matchBrackets: true,
    readOnly: true,
    extraKeys: { "Ctrl-Space": "autocomplete" }
  });
</script>
@endsection
