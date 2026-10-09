var data = null;

var xhr = new XMLHttpRequest();

xhr.addEventListener("readystatechange", function () {
  if (this.readyState === this.DONE) {
    console.log(this.responseText);
  }
});

xhr.open("GET", "https://api.climacell.co/v3/weather/forecast/daily?lat=20&lon=2&unit_system=si&start_time=2020-06-10T14%3A09%3A50Z&end_time=2020-06-17T14%3A09%3A50Z&fields=weather_code%2Ctemp&apikey=L6b87OjRlEwX6IozTwT2t3IENZ1xEgvN");

xhr.send(data);
