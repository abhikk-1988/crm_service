<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
$conn = mysqli_connect("localhost", "root", "bmhproduction@123!");
$GLOBALS['conn'] = $conn;
if (!$conn) {
    die("Database connection failed: " . mysqli_error());
}
$db_select = mysqli_select_db($conn, 'bmh_crm');
//    $db_select = mysqli_select_db($conn, 'testing_01');
if (!$db_select) {
    die("Database selection failed: " . mysqli_error());
}
$source = [];
$query = mysqli_query($conn, "select title from campaign_master where active_status = 1 & primary_campaign_id IS NOT NULL");
while ($result = mysqli_fetch_array($query)) {
    array_push($source, $result['title']);
}
?>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
        <style>

            .red{
                color:red;
            }
            .form-area
            {
                background-color: #FAFAFA;
                padding: 10px 40px 60px;
                margin: 10px 0px 60px;
                border: 1px solid GREY;
            }
        </style>
        <script type="text/javascript">


            var submit_form = []; // varibale to hold status of submitting form

            var datastring = {};

            datastring.postEnqueryData = {};

            function email_validation(email) {

//                var email_regex  = /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/;
                var name_regex1 = /^[^\s]+$/;
                        var email_regex = /^(?!\d+\b)+(([a-zA-Z0-9._-])+)\@(([a-zA-Z])+\.)+([a-zA-Z]{2,4})+$/;
//                        if (email === '') {
//                    submit_form.push('email');
//                    return false;
//                }
                        if (email != '') {
                    if (email.match(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/) && email.match(/^[^\s]+$/)) {
                        var i_index = submit_form.indexOf('email');
                        if (i_index > -1) {
                            submit_form.splice(i_index, 1);
                        }
                        return true;
                    }
                    else {
                        submit_form.push('email');
                        return false;
                    }
                }
                else {
                    return true;
                }
            }

            function text_validation(text) {
                var name_regex = /^[a-zA-Z-,](\s{0,1}[a-zA-Z-,])*[^\s]$/;
                //var name_regex = /^[a-zA-Z\s]+$/;

                if (text === '') {
                    submit_form.push('text');
                    return false;
                }

                if (text.match(name_regex) && !text.match(/[*|\":<>[\]{}`\\()';@&$]/)) {
                    var i_index = submit_form.indexOf('text');
                    if (i_index > -1) {
                        submit_form.splice(i_index, 1);
                    }
                    return true;
                }
                else {
                    submit_form.push('text');
                    return false;
                }

            }

            function phone_validation(phone) {
                var phone_regex = /^[0-9]+$/;
                var name_regex1 = /^[^\s]+$/;
//                var phone_regex = /^[0-9]+$/;
                if (phone === '') {
                    submit_form.push('phone');
                    return false;
                }

                if (phone.match(name_regex1) && phone.match(phone_regex)) {

                    var i_index = submit_form.indexOf('phone');
                    if (i_index > -1) {
                        submit_form.splice(i_index, 1);
                    }

                    return true;
                }
                else {
                    submit_form.push('phone');
                    return false;
                }

            }

            function message_validation(message, f_name) {
                var message_regex = /^[a-zA-Z0-9-,](\s{0,1}[a-zA-Z0-9-,])*[^\s]$/;
//                var message_regex = /^[a-zA-Z0-9\s]+$/;
                if (message === '') {
                    submit_form.push(f_name);
                    return false;
                }
//                if (message.match(message_regex) && !message.match(/[*|\":<>[\]{}`\\()';@&$]/)) {
//                if (message.match(message_regex) && !message.match(/[*|\":<>[\]{}`\\();$]/)) {   // comment on 08/09/2017 for remove vaidations
                if (message.match(message_regex)) {
                    var i_index = submit_form.indexOf(f_name);
                    if (i_index > -1) {
                        submit_form.splice(i_index, 1);
                    }
                    return true;
                }
                else {
                    submit_form.push(f_name);
                    return false;
                }
            }

            function callsource() {
                var e = document.getElementById("leadsource");
                var value = e.options[e.selectedIndex].value;
                var text = e.options[e.selectedIndex].text;
                if (value == 'other') {
                    $('#source').show();
                }
            }

            function ValidateEmail(email)
            {

                submit_form = [];

                var lead_form = document.getElementsByName('myForm')[0];

                for (var i = 0; i < lead_form.length; i++) {

                    if (lead_form[i].name === 'name') {
                        if (!text_validation(lead_form[i].value)) {
                            $('#name_help').text('Name is in alphabets only').css({color: '#F00'});
                        } else {
                            datastring.postEnqueryData.user_name = lead_form[i].value;
                            $('#name_help').text('');
                        }
                    }

                    if (lead_form[i].name === 'email') {
                        if (!email_validation(lead_form[i].value)) {
                            $('#email_help').text('Email is invalid').css({color: '#F00'});
                        } else {
                            datastring.postEnqueryData.user_email = lead_form[i].value.trim();
                            $('#email_help').text('');
                        }
                    }


                    if (lead_form[i].name === 'mobile') {
                        if (!phone_validation(lead_form[i].value)) {
                            $('#mobile_help').text('Please Provide correct Mobile Number').css({color: '#F00'});
                        } else {
                            datastring.postEnqueryData.user_contact = lead_form[i].value;
                            $('#mobile_help').text('');
                        }
                    }

                    if (lead_form[i].name === 'message') {
//                        if (!message_validation(lead_form[i].value, 'message')) {
//                            $('#message_help').text('Message is in Alphanumeric only').css({color: '#F00'});
//                        } else {
                        datastring.postEnqueryData.message = lead_form[i].value;
                        $('#message_help').text('');
//                        }
                    }

                    if (lead_form[i].name === 'project') {
//                        if (lead_form[i].value == '') {
//                            $('#project_help').text('Project is Required').css({color: '#F00'});
//                        } else {
                        datastring.postEnqueryData.project = lead_form[i].value;
                        $('#project_help').text('');
//                        }
                    }

                    if (lead_form[i].name === 'remark') {
//                        if (!message_validation(lead_form[i].value, 'remark')) {
//                            $('#remark_help').text('Remarks is in Alphanumeric only').css({color: '#F00'});
//                        } else {
                        datastring.postEnqueryData.remark = lead_form[i].value;
                        $('#remark_help').text('');
//                        }
                    }
                    if (lead_form[i].name === 'added_by') {
                        if (lead_form[i].value != '') {
                            datastring.postEnqueryData.added_by = lead_form[i].value;
                            $('#added_help').text('');
                        }
                        else {
                            $('#added_help').text('Executive is Required').css({color: '#F00'});
                        }

                    }
                    if (lead_form[i].name === 'leadsource') {
                        if (lead_form[i].value != '') {
                            datastring.postEnqueryData.leadsource = lead_form[i].value;
                            $('#source_help').text('');
                        }
                        else {
                            $('#source_help').text('Source is Required').css({color: '#F00'});
                        }
                    }
                    if (lead_form[i].name === 'source') {

                        if (lead_form[i].value != '') {
                            datastring.postEnqueryData.source = lead_form[i].value;
                            $('#source_help').text('');
                        }
                    }

                }

                if (submit_form.length == 0) {
                    datastring.key = $('#leadsource').val();
//                    alert(JSON.stringify(datastring));
                    $.ajax({
                        type: "post",
//                        url: "https://demo.bookmyhouse.co/crm/apis/save_enquiry_new.php",
                         url: "https://demo.bookmyhouse.co/crm/apis/save_enquiry.php",
                        data: {enquery: JSON.stringify(datastring)},
                        success: function (response) {

                            response_value = JSON.parse(response);
                            if (response_value.action == 'error') {
                                alert(response_value.message);
                            } else {
                                alert('data has been saved');

                                for (var i = 0; i < lead_form.length; i++) {
                                    if (lead_form[i].name != 'submit') {
                                        lead_form[i].value = '';
                                    }
                                }
                            }


                        }
                    });
                }


            }




        </script>
    </head>
    <body>
        <div class="container">
            <div class="col-md-5" style="left:30%;top:30px;">
                <div class="form-area">  
                    <form role="form" name="myForm">
                        <br style="clear:both">
                        <h3 style="margin-bottom: 25px; text-align: center;">Add Enquiry</h3>
                        <div class="form-group">
                            <input type="text" class="form-control" id="name" name="name"  pattern="[a-zA-Z\s]+" placeholder="Enter Name" required>
                        </div>
                        <span class="help-block" id="name_help"></span>
                        <div class="form-group">
                            <input type="text" class="form-control" id="email" name="email" placeholder="Enter Email">
                        </div>
                        <span class="help-block" id="email_help"></span>

                        <div class="form-group">
                            <input type="phone" class="form-control" id="mobile" name="mobile" pattern="\d+" placeholder="Enter Phone" required>
                        </div>
                        <span class="help-block" id="mobile_help"></span>

                        <div class="form-group">
                            <input type="text" class="form-control" id="project" name="project" pattern="[a-zA-Z0-9\s]+" placeholder="Enter Project" required>
                        </div>
                        <span class="help-block" id="project_help"></span>
                        <div class="form-group">
                            <input type="text" class="form-control" id="message" name="message" pattern="[a-zA-Z0-9\s]+" placeholder="Enter Message" required>
                        </div>
                        <span class="help-block" id="message_help"></span>
                        <div class="form-group">
                            <input type="text" class="form-control" id="remarks" name="remark"  placeholder="Enter Remarks" required>                
                        </div>
                        <span class="help-block" id="remark_help"></span>

                        <div class="form-group">

                            <select name="leadsource" id="leadsource" class="form-control" required>
                                <option value="">Select Source</option>
                                <?php foreach ($source as $leadsource) { ?>
                                    <option value="<?php echo $leadsource; ?>"><?php echo $leadsource; ?></option>

                                    <?php
                                }
                                ?>
                            </select>
                        </div>   
                        <span class="help-block" id="source_help"></span>
                        <div class="form-group">
                            <input type="text" class="form-control" id="source" name="source" placeholder="Enter Source" required style="display:none;">                
                        </div>
                        <span class="help-block" id="source_help"></span>

                        <div class="form-group">
                            <select name="added_by" id="executive" class="form-control" required/>
                            <option value="">Select Executive</option>
                            <option value="Vikash">Vikash</option>
                            <option value="Ashish">Ashish</option>
                            <option value="Sonal">Sonal</option>
                            <option value="Vijay">Vijay</option>
                            <option value="Rajkumari">Rajkumari</option>
                            </select>
                        </div>
                        <span class="help-block" id="added_help"></span>

                        <input type="button" name="submit" id="submit" class="btn btn-primary pull-right submit" value="Submit Form" onclick="ValidateEmail(document.myForm.email)"/>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>


