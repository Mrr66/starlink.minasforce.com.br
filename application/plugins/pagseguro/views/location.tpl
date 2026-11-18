<div class="row">



    <div class="col-md-6">


        <div class="ibox float-e-margins" id="ib_box">
            <div class="ibox-title">

                <h5>Get Location ID</h5>
            </div>
            <div class="ibox-content">

                <form role="form" method="post" id="ib_form" action="{$_url}pagseguro/init/get_location/">
                    <div class="form-group">
                        <label for="access_token">Access Token</label>
                        <input type="text" class="form-control" name="access_token" id="access_token">
                    </div>


                    <button type="submit" id="ib_submit" class="btn btn-primary">Submit</button>
                </form>



                <div id="resp">

                </div>


            </div>
        </div>




    </div>

</div>