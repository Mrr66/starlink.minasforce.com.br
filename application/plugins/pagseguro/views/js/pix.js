$(document).ready(function() {
    // Simular carregamento
    $('#spinner').addClass('show');
    $('#pixSelect').click(function() {
      location.href = window.location.href.replace("make","pix");
  });
   $('#copyBtn').click(function() {
                const pixKey = $('#pixKeyInput').val();
                
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(pixKey).then(function() {
                        const btn = $('#copyBtn');
                        const originalHtml = btn.html();
                        
                        btn.addClass('copied');
                        btn.html('<i class="fas fa-check"></i> Copiado!');
                        
                        setTimeout(function() {
                            btn.removeClass('copied');
                            btn.html(originalHtml);
                        }, 5000);
                    }).catch(function(err) {
                        alert('Erro ao copiar: ' + err);
                    });
                } else {
                    // Fallback para navegadores antigos
                    const textarea = document.getElementById('pixKeyInput');
                    textarea.select();
                    document.execCommand('copy');
                    
                    const btn = $('#copyBtn');
                    const originalHtml = btn.html();
                    
                    btn.addClass('copied');
                    btn.html('<i class="fas fa-check"></i> Copiado!');
                    
                    setTimeout(function() {
                        btn.removeClass('copied');
                        btn.html(originalHtml);
                    }, 5000);
                }
            });

    setTimeout(function() {
        $('#spinner').removeClass('show');
    }, 1500);

    setInterval(function() {
        var form = new FormData();
        let id = location.search.split("/")[3];
        form.append("key", "2g0dzxbyr7zpbt6je028ou3p88qsutsqnd0jhaha");
        form.append("method", "get_invoice_details_by_id");
        form.append("id", id);

        var settings = {
          "url": location.origin+"/?ng=api",
          "method": "POST",
          "timeout": 0,
          "processData": false,
          "mimeType": "multipart/form-data",
          "contentType": false,
          "data": form
        };
        
        $.ajax(settings).done(function (response) {
          let status = JSON.parse(response).result.shift().status;
          if(status == "Paid")
            location.href = location.href.replace("pagseguro/payments/pix", "client/iview");
        });
    }, 4000);
});