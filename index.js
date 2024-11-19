var timeout = 60000;
function sync_attendance(){
    save_attendance(function () {
        setTimeout(function () {
            send_attendance_on_cloud(function () {
                setTimeout(function () {
                    sync_attendance();
                },timeout);
            },function () {
                setTimeout(function () {
                    sync_attendance();
                },timeout);
            });
        },5000);


    },function () {
        setTimeout(function () {
            sync_attendance();
        },timeout);
    });
}

sync_attendance();