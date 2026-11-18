<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento PIX - Minas Force Pay</title>
    <link rel="icon" href="https://minasforce.com.br/wp-content/uploads/2023/12/cropped-cropped-logo-dark-32x32.png" sizes="32x32" />
    <link rel="icon" href="https://minasforce.com.br/wp-content/uploads/2023/12/cropped-cropped-logo-dark-192x192.png" sizes="192x192" />
    <link rel="apple-touch-icon" href="https://minasforce.com.br/wp-content/uploads/2023/12/cropped-cropped-logo-dark-180x180.png" />
    <meta name="msapplication-TileImage" content="https://minasforce.com.br/wp-content/uploads/2023/12/cropped-cropped-logo-dark-270x270.png" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            padding: 20px;
        }

        .payment-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 500px;
            width: 100%;
            overflow: hidden;
        }

        .payment-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }

        .payment-header h1 {
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .payment-header p {
            font-size: 14px;
            opacity: 0.9;
            margin: 0;
        }

        .payment-body {
            padding: 30px 20px;
        }

        .order-info {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 12px;
            font-size: 14px;
        }

        .order-info-row:last-child {
            margin-bottom: 0;
        }

        .order-info-label {
            color: #666;
            font-weight: 500;
        }

        .order-info-value {
            color: #333;
            font-weight: 600;
        }

        .order-info-value.amount {
            color: #667eea;
            font-size: 18px;
        }

        .qr-code-section {
            text-align: center;
            margin-bottom: 25px;
        }

        .qr-code-label {
            font-size: 14px;
            color: #666;
            font-weight: 600;
            margin-bottom: 15px;
            display: block;
        }

        .qr-code-container {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            display: inline-block;
            margin-bottom: 20px;
        }

        .qr-code-container img {
            max-width: 100%;
            height: auto;
            display: block;
        }

        .pix-key-section {
            margin-bottom: 25px;
        }

        .pix-key-label {
            font-size: 14px;
            color: #666;
            font-weight: 600;
            margin-bottom: 10px;
            display: block;
        }

        .pix-key-container {
            display: flex;
            gap: 10px;
            align-items: center;
            text-align: center;
        }

        .pix-key-input {
            flex: 1;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            color: #333;
            background: #f8f9fa;
            word-break: break-all;
            overflow-y: auto;
            max-height: 80px;
            resize: none;
        }

        .pix-key-input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
        }

        .copy-btn {
            padding: 12px 20px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 18px;
            white-space: nowrap;
        }

        .copy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }

        .copy-btn:active {
            transform: translateY(0);
        }

        .copy-btn.copied {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .expiration-info {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 15px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 13px;
            color: #856404;
        }

        .expiration-info i {
            margin-right: 8px;
        }

        .payment-instructions {
            background: #e7f3ff;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .payment-instructions h5 {
            font-size: 14px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .payment-instructions ol {
            margin: 0;
            padding-left: 20px;
            font-size: 13px;
            color: #555;
        }

        .payment-instructions li {
            margin-bottom: 8px;
        }

        .payment-instructions li:last-child {
            margin-bottom: 0;
        }

        .status-badge {
            display: inline-block;
            background: #fff3cd;
            color: #856404;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.approved {
            background: #d4edda;
            color: #155724;
        }

        .payment-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-top: 1px solid #e0e0e0;
        }

        .payment-footer p {
            font-size: 12px;
            color: #999;
            margin: 0;
        }

        .payment-footer a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }

        .payment-footer a:hover {
            text-decoration: underline;
        }

        .item-list {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .item-list-title {
            font-size: 13px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
            font-size: 13px;
        }

        .item:last-child {
            border-bottom: none;
        }

        .item-name {
            color: #555;
            flex: 1;
        }

        .item-quantity {
            color: #999;
            margin: 0 10px;
            font-size: 12px;
        }

        .item-price {
            color: #333;
            font-weight: 600;
        }

        .payer-info {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .payer-info-title {
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .payer-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            color: #555;
        }

        .payer-info-row:last-child {
            margin-bottom: 0;
        }

        .payer-info-label {
            font-weight: 600;
            color: #666;
        }

        @media (max-width: 480px) {
            .payment-container {
                border-radius: 12px;
            }

            .payment-header {
                padding: 25px 15px;
            }

            .payment-header h1 {
                font-size: 20px;
            }

            .payment-body {
                padding: 20px 15px;
            }

            .pix-key-container {
                flex-direction: column;
            }

            .copy-btn {
                width: 100%;
                font-size: 19px;
            }

            .qr-code-container {
                padding: 15px;
            }
        }

        .spinner {
            display: none;
            text-align: center;
            margin: 20px 0;
        }

        .spinner.show {
            display: block;
        }

        .loader {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <!-- Header -->
        <div class="payment-header">
            <h1>Pagamento</h1>
        </div>

        <!-- Body -->
        <div class="payment-body">

            <!-- PIX Key Section -->
            <div class="pix-key-section">
                <label class="pix-key-label">Selecione e forma de pagamento</label>
                <div class="pix-key-container">
                    <button class="copy-btn" id="pixSelect">
                        <i class="fas fa-qrcode"></i> Pagar com PIX
                    </button>
                    <button class="copy-btn" id="boletoSelect">
                        <i class="fas fa-file-text"></i> Pagar com Boleto
                    </button>
                </div>
            </div>
            <!-- Payer Info -->
            <div class="payer-info" id="payerContainer" style="display: none;">
                <div class="payer-info-title">Informações do Cliente</div>
                <div class="payer-info-row">
                    <span class="payer-info-label">Nome:</span>
                    <span id="payerName">-</span>
                </div>
                <div class="payer-info-row">
                    <span class="payer-info-label">E-mail:</span>
                    <span id="payerEmail">-</span>
                </div>
                <div class="payer-info-row">
                    <span class="payer-info-label">Telefone:</span>
                    <span id="payerPhone">-</span>
                </div>
            </div>

            <!-- Spinner -->
            <div class="spinner" id="spinner">
                <div class="loader"></div>
                <p style="color: #999; font-size: 14px; margin-top: 10px;">Carregando dados...</p>
            </div>
        </div>

        <!-- Footer -->
        <div class="payment-footer">
            <p>Pagamento seguro com <strong>Minas Force Pay</strong></p>
            <p style="margin-top: 8px; font-size: 11px;">Desenvolvido por: Minas Force LTDA</p>
        </div>
    </div>
</body>
</html>
{include file="application/plugins/pagseguro/views/footer_default.tpl"}