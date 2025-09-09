<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>PHPSchema Demo</title>
    <style>
        .fields-box {
            background: #f0f4fa;
            border: 5px solid #c3d0e8;
            border-radius: 6px;
            padding: 18px 14px 10px 14px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .fields-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            border-bottom: 5px solid #c3d0e8;
            padding-bottom: 16px;
        }
        .top-row {
            display: flex;
            align-items: flex-end;
            gap: 12px;
            margin-bottom: 18px;
        }
        #send-request {
            margin-left: auto;
            margin-bottom: 0;
        }
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            padding: 32px 24px 24px 24px;
        }
        .logo {
            display: block;
            margin: 0 auto 24px auto;
            width: 120px;
        }
        h2 {
            text-align: center;
            color: #333;
        }
        .form-row {
            display: flex;
            gap: 8px;
            margin-bottom: 8px;
        }
        .form-row input[type=text] {
            flex: 1;
            padding: 6px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-row button,
        #add-field,
        #send-request {
            padding: 10px 12px;
            border: none;
            background: #0078d7;
            color: #fff;
            border-radius: 4px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .form-row button.remove {
            background: #e74c3c;
        }
        #add-field {
            background: #27ae60;
        }
        #add-field:hover {
            background: #219150;
        }
        #send-request {
            background: #0078d7;
        }
        #send-request:hover {
            background: #005fa3;
        }
        .form-row button.remove:hover {
            background: #c0392b;
        }
        .endpoint-select {
            width: 100%;
            padding: 8px;
            margin-top: 16px;
            margin-bottom: 16px;
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        #response {
            background: #f0f4fa;
            border: 5px solid #ddd;
            border-radius: 4px;
            padding: 12px;
            margin-top: 16px;
            min-height: 40px;
            font-family: monospace;
            white-space: pre-wrap;
            color: #29a300ff;
        }
        #schema-configured {
            background: #f0f4fa;
            border: 5px solid #ddd;
            border-radius: 4px;
            padding: 12px;
            margin-top: 16px;
            min-height: 40px;
            font-family: monospace;
            white-space: pre-wrap;
            color: #d2a503ff;
        }
    </style>
</head>
<body>
    <div class="container">
        <img src="../assets/project-logo.png" alt="PHPSchema Logo" class="logo">
        <h2>Demo PHPSchema</h2>
        <div class="top-row">
            <div style="flex:1;">
                <label for="endpoint">Selecione um schema para validar:</label>
                <select id="endpoint" class="endpoint-select" style="margin-bottom:0;">
                    <option value="example-1">Cadastro de Usuário</option>
                    <option value="example-2">Lista de eventos</option>
                </select>
            </div>
            <button type="submit" id="send-request" form="payload-form">Validar Schema</button>
        </div>
        <label for="schema-configured">Schema configurado:</label>
        <div id="schema-configured" style="margin-bottom:18px;"></div>
        <form id="payload-form" autocomplete="off">
            <div class="fields-box">
                <div class="fields-header">
                    <span>Campos do payload</span>
                    <button type="button" id="add-field" title="Adicionar campo">
                        <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="none" viewBox="0 0 20 20" style="vertical-align:middle;"><circle cx="10" cy="10" r="9" fill="#fff"/><path d="M10 5v10M5 10h10" stroke="#27ae60" stroke-width="2" stroke-linecap="round"/></svg>
                        Adicionar campo
                    </button>
                </div>
                <div id="fields"></div>
            </div>
        </form>
        <label for="response">Response do PHPSchema:</label>
        <div id="response"></div>
    </div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        let fieldCount = 0;
        function createFieldRow(key = '', value = '') {
            return `<div class="form-row" data-row>
                <input type="text" name="key" placeholder="Chave" value="${key}" required pattern="^(?!endpoint$).*$" title="Não pode ser 'endpoint'">
                <input type="text" name="value" placeholder="Valor" value="${value}">
                <button type="button" class="remove">Remover</button>
            </div>`;
        }
        function updateRemoveButtons() {
            $('#fields .form-row').each(function() {
                $(this).find('.remove').off('click').on('click', function() {
                    $(this).parent().remove();
                });
            });
        }
        $(function() {
                    $('#add-field').on('click', function() {
                        $('#fields').append(createFieldRow());
                        updateRemoveButtons();
                    });
                    // Adiciona um campo inicial
                    $('#fields').append(createFieldRow());
                    updateRemoveButtons();

                    // Atualiza texto do schema-configured
                    function updateSchemaText() {
                        var endpoint = $('#endpoint').val();
                        var text = '';
                        if(endpoint === 'example-1') {
                            text = 'Configuração do schema example-1: exemplo de texto.';
                        } else if(endpoint === 'example-2') {
                            text = 'Configuração do schema example-2: exemplo de texto.';
                        }
                        $('#schema-configured').text(text);
                    }
                    updateSchemaText();
                    $('#endpoint').on('change', updateSchemaText);

                    $('#payload-form').on('submit', function(e) {
                        e.preventDefault();
                        let endpoint = $('#endpoint').val();
                        let payload = {};
                        let valid = true;
                        $('#fields .form-row').each(function() {
                            let key = $(this).find('input[name=key]').val().trim();
                            let value = $(this).find('input[name=value]').val();
                            if(key === 'endpoint') {
                                valid = false;
                                $(this).find('input[name=key]')[0].setCustomValidity("Não pode ser 'endpoint'");
                                $(this).find('input[name=key]')[0].reportValidity();
                            } else {
                                $(this).find('input[name=key]')[0].setCustomValidity("");
                                payload[key] = value;
                            }
                        });
                        if(!valid) return;
                        $.ajax({
                            url: 'api.php',
                            method: 'POST',
                            data: { endpoint, ...payload },
                            dataType: 'json',
                            success: function(resp) {
                                $('#response').text(JSON.stringify(resp, null, 2));
                            },
                            error: function(xhr) {
                                let msg = xhr.responseText || 'Erro ao fazer request';
                                $('#response').text(msg);
                            }
                        });
                    });
                });
    </script>
</body>
</html>
