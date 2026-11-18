
{include file="sections/header.tpl"}
<html>
    <head>
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.2/emojionearea.min.js" integrity="sha512-hkvXFLlESjeYENO4CNi69z3A1puvONQV5Uh+G4TUDayZxSLyic5Kba9hhuiNLbHqdnKNMk2PxXKm0v7KDnWkYA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/emojionearea/3.4.2/emojionearea.min.css" integrity="sha512-vEia6TQGr3FqC6h55/NdU3QSM5XR6HSl5fW71QTKrgeER98LIMGwymBVM867C1XHIkYD9nMTfWK2A0xcodKHNA==" crossorigin="anonymous" referrerpolicy="no-referrer" />

        
        <style>
            table {
                width: 100%;
            }
            th, td {
                width: 33.33%;
                padding: 10px;
            }
            td {
                border: 1px solid rgb(236, 236, 236); 
            }
        </style>

        
    </head>
    <body>
    <div class="row">
        <div class="col-md-12">
            <div class="ibox float-e-margins">
                <div class="ibox-title">
                    <h5>{$_L['Charges']}</h5>
                </div>
                <div class="ibox-content">

                    <form action="#" method="post">
                        <table class="table table-striped table-bordered" >
                            <tr>
                                <td colspan="2">
                                    <label for="text">{$_L['Text']}</label>
                                    <div id="container">
                                        <textarea id="my-text"></textarea>          
                                    </div>
                                </td>
                            </tr>

                            <tr> 
                                <td>
                                    <label for="text">{$_L['Repeat when']}</label>
                                    <div>
                                        <select name="type" class="form-control" id="inlineFormCustomSelect">
                                            <option value="-2" selected>2 dias antes</option>
                                            <option value="-1">1 dia antes</option>
                                            <option value="0">No dia</option>
                                            <option value="1">1 dia depois</option>
                                            <option value="2">2 dias depois</option>
                                            <option value="3">3 dias depois</option>
                                            <option value="4">4 dias depois</option>
                                            <option value="5">5 dias depois</option>        
                                        </select>
                                    </div>
                                </td> 
                            </tr>
    
                            <tr>
                                <td colspan="2">
                                    <button class="btn btn-info btn-larger" id="button-form" type="submit">{$_L['Add']}</button>
                                </td>
                            </tr>
                        </table>  
                        <table id="ruler-charges">
                            <tr>
                                <th>Dias</th>
                                <th>Mensagem</th>
                                <th>Excluir</th>
                            </tr>

                        </table>
                    
                    </form>
                    
                </div>
            </div>



        </div>



    </div>


    <input type="hidden" id="_lan_are_you_sure" value="{$_L['are_you_sure']}">

    <script type="text/javascript" language="Javascript">
        $("#my-text").emojioneArea({
          pickerPosition: 'bottom'
        });

        const buttonSubmit = document.querySelector("#button-form");
        buttonSubmit.onclick = function() {
            event.preventDefault();
            let text = document.querySelector("#my-text").value;
            document.querySelector("#my-text").value = ""; 
            const repeatWhen = document.querySelector("#inlineFormCustomSelect").value;
            
            const td1 = document.createElement("td");
            const repeat = document.createTextNode(repeatWhen);
            td1.appendChild(repeat);
            
            const td2 = document.createElement("td");
            const texto = document.createTextNode(text);
            td2.appendChild(texto);
            text = "";

            const td3 = document.createElement("td");
            const textDel = document.createTextNode("Deletar");
            const button = document.createElement("button");
            const randomId = Math.random().toString();
            button.appendChild(textDel)
            button.classList.add("btn", "btn-danger", "btn-xs", "cdelete");
            button.id = randomId;
            td3.appendChild(button)
            

            const tr = document.createElement("tr");
            tr.appendChild(td1);
            tr.appendChild(td2);
            tr.appendChild(td3);

            const table = document.querySelector("#ruler-charges");
            table.appendChild(tr);

            const btnDelete = document.getElementById(randomId);
            btnDelete.onclick = deleteLine;
        }
        
        function deleteLine() {
            event.preventDefault();
            const btn = document.getElementById(this.id);
            const td = btn.parentNode;
            const tr = td.parentNode;
            const table = tr.parentNode;
            table.removeChild(tr);
        }

    </script>
      
    </body>
</html>
{include file="sections/footer.tpl"}
