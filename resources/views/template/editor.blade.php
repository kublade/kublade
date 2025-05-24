<textarea id="editor" class="form-control" rows="10" name="content">{!! $file->content !!}</textarea>
<div class="alert alert-info m-3 d-flex align-items-center gap-3">
  <i class="bi bi-info-circle fs-5"></i>
  <div>
    Press <strong>Ctrl-Space</strong> to show available code blocks and variables
  </div>
</div>

@section('css')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/codemirror.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/hint/show-hint.min.css">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/5.65.2/addon/hint/show-hint.min.js"></script>
<script>
  const bladeDirectives = [
    
  ];

  const echoVariables = [
    "user", "auth", "route()", "url()", "now()", "old()", "session()", "csrf_token()"
  ];

  function customCompletion(cm) {
    const cur = cm.getCursor();
    const token = cm.getTokenAt(cur);
    const start = token.start;
    const end = cur.ch;
    const word = token.string;

    const bladeDirectives = [
      '@@if()', '@@elseif()', '@@else', '@@endif',
      '@@foreach( as )', '@@endforeach', '@@for()', '@@endfor', '@@forelse( as )',
      '@@while()', '@@endwhile', '@@switch()', '@@case()', '@@break',  '@@continue', '@@endswitch', '@@default',
      '@@include()', '@@extends()', '@@section()', '@@yield()', '@@endsection', '@@show', '@@parent',
      '@@csrf', '@@method()', '@@error()', '@@enderror', '@@auth', '@@guest', '@@endauth', '@@endguest',
      '@@isset()', '@@empty()', '@@endisset', '@@endempty', '@@push()', '@@pushOnce()', '@@stack()', '@@endpush',
      '@@can()', '@@cannot()', '@@endcan', '@@php', '@@endphp',
    ];

    const variables = [
      @foreach ($template->fields as $field)
        @if ($field->secret)
          '@{{ $secret[\'{!! $field->key !!}\'] }}',
        @else
          '@{{ $data[\'{!! $field->key !!}\'] }}',
        @endif
      @endforeach
      @foreach ($template->ports as $port)
        '@{{ $ports[\'{!! $port->claim !!}\'] }}',
      @endforeach
    ];

    let completions = [...variables, ...bladeDirectives];

    return {
      list: completions.length ? completions : ['<keine Treffer>'],
      from: CodeMirror.Pos(cur.line, end),
      to: CodeMirror.Pos(cur.line, end)
    };
  }

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
    extraKeys: {
      "Ctrl-Space": function(cm) {
        cm.showHint({ hint: customCompletion });
      }
    }
  });
</script>
@endsection
