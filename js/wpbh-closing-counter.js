/*
 Counter to display closing time
 */
var div = document.getElementById('closing-target');
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

        var hours = Math.floor((distance % _day) / _hour);
        var minutes = Math.floor((distance % _hour) / _minute);
        var seconds = Math.floor((distance % _minute) / _second);

        if (hours == 0) {
            hours = ' ';
        } else {
            hours = ' ' + hours + ' h ';
        }

        document.getElementById('closing-countdown').innerHTML = hours;
        document.getElementById('closing-countdown').innerHTML += minutes + ' min ';
        document.getElementById('closing-countdown').innerHTML += seconds + ' sec';


    }
}

timer = setInterval(showRemaining, 1000);
