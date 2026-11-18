
{include file="sections/header.tpl"}
<div class="row">
    <div class="col-md-12">
        <div class="ibox float-e-margins">
            <div class="ibox-title">
                <h5>{$_L['settings']}</h5>
            </div>
            <div class="ibox-content">
                <div class="alert alert-success" id="emsg">
                    <span id="emsgbody"></span>
                </div>
                <table class="table table-striped table-bordered">
                    <thead>
                        <th>{$_L['Client Id']}</th>
                        <th>{$_L['Secrety key']}</th>
                        <th>{$_L['Group']}</th>
                        <th>{$_L['Action']}</th>
                    </thead>
                    <tbody>
                        <form action="{$_url}whatsapp/config-post" method="post" id="tform" role="form">
                            <tr>
                                <td>
                                    <input type="text" class="form-control" name="clientId" id="clientId" value="{$config->clienteId}" />
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="secretId" id="secretId" value="{$config->key}" />
                                </td>
                                <td>
                                    <input type="text" class="form-control" name="group" id="group" value="{$config->group}"/>
                                </td>
                                <td>
                                    <button class="btn btn-info btn-larger type="submit" id="submit">{$_L['Save']}</button>
                                </td>
                            </tr>
                        </form>
                    </tbody>
                </table>

            </div>
        </div>
    </div>



</div>


<input type="hidden" id="_lan_are_you_sure" value="{$_L['are_you_sure']}">

{include file="sections/footer.tpl"}
