/*
 Counter to display opening time
 */
var div = document.getElementById('opening-target');
if (div) {
    var end = new Date(div.textContent);

    var _second = 1000;
    var _minute = _second * 60;
    var _hour = _minute * 60;
    var _day = _hour * 24;
    var timer;

    function showRemaining() {
        var now = new Date();
        var distance = end - now;

        if (distance < 0) {

            clearInterval(timer);
            document.getElementById('opening-countdown').innerHTML = ' ' + object_msg.future_msg;

            return;
        }

        var hours = Math.floor((distance % _day) / _hour);
        var minutes = Math.floor((distance % _hour) / _minute);
        var seconds = Math.floor((distance % _minute) / _second);


        if (hours == 0) {
            hours = ' ';
        } else {
            hours = ' ' + hours + ' h ';
        }


        document.getElementById('opening-countdown').innerHTML = hours;
        document.getElementById('opening-countdown').innerHTML += minutes + ' min ';
        document.getElementById('opening-countdown').innerHTML += seconds + ' sec';


    }
}
timer = setInterval(showRemaining, 1000);
