<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Flow Editor</title>
    @viteReactRefresh
    @vite(['resources/js/flow-editor/index.jsx'])
    <style>
        body, html { margin: 0; padding: 0; height: 100%; overflow: hidden; }
        #flow-editor-root { width: 100%; height: 100%; }
    </style>
</head>
<body>
    <div id="flow-editor-root" data-module-id="{{ $compositeModule->id }}"></div>
</body>
</html>
