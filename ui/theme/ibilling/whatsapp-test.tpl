
{include file="sections/header.tpl"}
<div class="row">
    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{$_L['Test']}</h5>
            </div>
            <div class="ibox-content">

                <form action="{$_url}whatsapp/test_send" method="post">
                    <table class="table table-striped table-bordered">
                        <tr>
                            <td style="width: 20%;">    
                                <label for="codPais">{$_L['Country code']}</label>
                                <input type="number" name="codPais" id="codPais" class="form-control" placeholder="Ex: 55">
                            </td>
                            <td style="width: 20%;">    
                                <label for="ddd">{$_L['DDD']}</label>
                                <input type="number" name="ddd" id="ddd" class="form-control" placeholder="Ex: 31">
                            </td> 
                            <td style="width: 60%;">    
                                <label for="number">{$_L['Number']}</label>
                                <input type="number" name="number" id="number" class="form-control" placeholder="Ex: 986345678">
                            </td>
                        </tr>   
                        <tr>
                            <td colspan="3">
                                <label for="tel">{$_L['Type']}</label>
                                <select name="type" class="form-control" id="inlineFormCustomSelect">
                                    <option value="0" selected>whatsapp</option>
                                    <option value="1">SMS</option>
                                    <option value="2">whatsapp e SMS</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="3">
                                <label for="text">{$_L['Text']}</label>
                                <div>
                                    <textarea name="message" id="message" style="width: 100%; resize: none;"></textarea>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <button class="btn btn-info btn-larger type="submit>{$_L['Send']}</button>
                            </td>
                        </tr>
                    </table>  

                
                </form>
                
            </div>
        </div>



    </div>



</div>


<input type="hidden" id="_lan_are_you_sure" value="{$_L['are_you_sure']}">

{include file="sections/footer.tpl"}
