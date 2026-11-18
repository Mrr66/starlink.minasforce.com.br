$(document).ready(function() {
    $('#pixSelect').click(function() {
      location.href = window.location.href.replace("make","pix");
    });

    $('#boletoSelect').click(function() {
        alert("A função para pagamento via boleto não está ativo para você. Você tem duas opções use o PIX, ou solicitar ativar o boleto. (Fale com Vinicius 📲)");
    })

  setTimeout(function() {
                // Preencher dados do pagamento
                // Esconder spinner
                $('#spinner').removeClass('show');
            }, 500);
});