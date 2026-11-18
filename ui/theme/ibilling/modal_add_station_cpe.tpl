<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
    <h3>
        {if $f_type eq 'edit'}
            {$_L['Edit']}
        {else}
            {$_L['Create Dog']}
        {/if}
    </h3>
</div>
<div class="modal-body row">

    <form class="form-horizontal" id="ib_modal_form">
            <div class="col-md-4">
                <img src="{$app_url}/application/storage/system/cao.jpg" width="100%">
            </div>
            <div class="col-md-8">
               <div class="row">
               <br />
               <select id="cid" name="cid" class="form-control">
                   <option value="">{$_L['Select Device']}...</option>
                    {foreach $c as $cs}
                        <option value="{$cs['id']}">
                           {$cs['ip']} - {$cs['nome']} 
                        </option>
                    {/foreach}
               </select>
               <span class="help-block"><a href="#" id="contact_add">| {$_L['Or Add New Device']}</a> </span>
               </div>
            </div>
    </form>

</div>
<div class="modal-footer">



    <button type="button" data-dismiss="modal" class="btn btn-danger">{$_L['Cancel']}</button>
    <button class="btn btn-primary modal_submit" type="submit" id="modal_submit"><i class="fa fa-check"></i> {$_L['Save']}</button>
</div>