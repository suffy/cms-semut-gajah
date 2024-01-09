
<div class="footer">
    Copyright © Hotel Management System, 2020
</div>

<script>
    //Format ke ISO Standard
    function formatDateToISO(date) {
        var d = new Date(date),
            month = '' + (d.getMonth() + 1),
            day = '' + d.getDate(),
            year = d.getFullYear();

        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;

        return [year, month, day].join('-');
    }

    // Indonesia Format
    function formatDate(d) {

        var date = new Date(d);

        if (isNaN(date.getTime())) {
            return d;
        } else {

            var weekday = new Array(7);
            weekday[0] = "Minggu";
            weekday[1] = "Senin";
            weekday[2] = "Selasa";
            weekday[3] = "Rabu";
            weekday[4] = "Kamis";
            weekday[5] = "Jumat";
            weekday[6] = "Sabtu";

            var month = new Array();
            month[0] = "Januari";
            month[1] = "Februari";
            month[2] = "Maret";
            month[3] = "April";
            month[4] = "Mei";
            month[5] = "Juni";
            month[6] = "Juli";
            month[7] = "Agustus";
            month[8] = "September";
            month[9] = "October";
            month[10] = "November";
            month[11] = "Desember";

            day = date.getDate();

            if (day < 10) {
                day = "0" + day;
            }

            var hour;
            var minutes;
            var second;

            if (date.getHours() == 0) {
                hour = ""
            } else {
                hour = " | " + date.getHours() + ":";
            }

            if (date.getMinutes() == 0) {
                minutes = ""
            } else {
                minutes = date.getMinutes() + ":";
            }

            if (date.getSeconds() == 0) {
                second = ""
            } else {
                second = date.getSeconds();
            }

            // return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear() + "  " + hour + minutes + second;
            return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear();

        }

    }

    function formatDateTime(d) {

        var date = new Date(d);

        if (isNaN(date.getTime())) {
            return d;
        } else {

            var weekday = new Array(7);
            weekday[0] = "Minggu";
            weekday[1] = "Senin";
            weekday[2] = "Selasa";
            weekday[3] = "Rabu";
            weekday[4] = "Kamis";
            weekday[5] = "Jumat";
            weekday[6] = "Sabtu";

            var month = new Array();
            month[0] = "Januari";
            month[1] = "Februari";
            month[2] = "Maret";
            month[3] = "April";
            month[4] = "Mei";
            month[5] = "Juni";
            month[6] = "Juli";
            month[7] = "Agustus";
            month[8] = "September";
            month[9] = "October";
            month[10] = "November";
            month[11] = "Desember";

            day = date.getDate();

            if (day < 10) {
                day = "0" + day;
            }

            var hour;
            var minutes;
            var second;

            if (date.getHours() == 0) {
                hour = ""
            } else {
                hour = " | " + date.getHours() + ":";
            }

            if (date.getMinutes() == 0) {
                minutes = ""
            } else {
                minutes = date.getMinutes() + ":";
            }

            if (date.getSeconds() == 0) {
                second = ""
            } else {
                second = date.getSeconds();
            }

            return weekday[date.getDay()] + ", " + day + " " + month[date.getMonth()] + " " + date.getFullYear() + "  " + hour + minutes + second;

        }

    }

    function nominalToCurrency(number)
    {
        number = number.toFixed(2) + '';
        x = number.split('.');
        x1 = x[0];
        x2 = x.length > 1 ? '.' + x[1] : '';
        var rgx = /(\d+)(\d{3})/;
        while (rgx.test(x1)) {
            x1 = x1.replace(rgx, '$1' + ',' + '$2');
        }
        return x1 + x2;
    }
</script>