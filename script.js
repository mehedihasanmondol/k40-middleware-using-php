function saveUser() {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            document.getElementById("status").innerHTML = this.responseText;
        }
    };
    xmlhttp.open("GET", "ajax-save-attendance.php",true);
    xmlhttp.send();
}

function send_request(url,call_back,fail=function () {}) {
    var xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() {
        if (this.readyState == 4 && this.status == 200) {
            call_back(this.responseText);
        }

    };
    xmlhttp.open("GET", url,true);
    xmlhttp.send();
}

function process_start(process_name) {
    document.getElementById("process_heading").innerHTML = process_name;
    process_response_update(process_name+ " starting....")
}
function process_response_update(response) {
    document.getElementById("process_response").innerHTML = response;
}

function save_attendance(call_back,new_not_found=function () {}) {
    process_start("Saving attendance");
    send_request("ajax-save-attendance.php",function (response) {
        var message = response;
        if (response == 1){
            message = "Done";
            call_back();
        }
        else if (response == 0) {
            new_not_found();
            message = "Don't have any new attendance records.";
        }
        process_response_update(message)
    })
}

function send_attendance_on_cloud(call_back,failed_call_back=function () {}) {
    process_start("Sending attendance on cloud");
    send_request("ajax-send-attendance-on-cloud.php",function (response) {
        var message = response;
        if (response == 1){
            message = "Done";
            call_back();
        }
        else if (response == 0){
            message = "Failed";
            failed_call_back();
        }

        process_response_update(message)
    })
}
