<textarea id="editor" class="form-control" rows="10" name="content">{!! $file->content !!}</textarea>

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/theme/dracula.min.css">
<style>
  .cm-blade-variable {
      color: #e06c75;
      font-weight: bold;
    }

    .cm-blade-directive {
      color: #61afef;
      font-style: italic;
    }

    .CodeMirror {
      height: auto;
      border: 1px solid #ccc;
      font-size: 14px;
      border-radius: 0.5rem;
      margin: 0 1rem;
    }
</style>
@endsection

@section('javascript')
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/mode/yaml/yaml.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/mode/overlay.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/matchbrackets.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/edit/closebrackets.min.js"></script>
<script>
  CodeMirror.defineMode("blade-overlay", function() {
    return {
      token: function(stream) {
        if (stream.match("{{")) {
          while ((ch = stream.next()) != null) {
            if (ch === "}" && stream.peek() === "}") {
              stream.next();
              break;
            }
          }
          return "blade-variable";
        }

        if (stream.match(/@\w+/)) {
          return "blade-directive";
        }

        while (stream.next() != null &&
               !stream.match("{{", false) &&
               !stream.match(/@\w+/, false)) {}
        return null;
      }
    };
  });

  CodeMirror.defineMode("yaml-blade", function(config) {
    const yaml = CodeMirror.getMode(config, "yaml");
    const blade = CodeMirror.getMode(config, "blade-overlay");

    return CodeMirror.overlayMode(yaml, blade);
  });

  var editor = CodeMirror.fromTextArea(document.getElementById("editor"), {
    mode: 'yaml-blade',
    lineNumbers: true,
    indentUnit: 2,
    tabSize: 2,
    lineWrapping: true,
    autoCloseBrackets: true,
    matchBrackets: true,
    extraKeys: { "Ctrl-Space": "autocomplete" }
  });
</script>
@endsection
