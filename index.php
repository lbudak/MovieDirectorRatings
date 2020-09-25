<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Movie Director Ratings</title>
    <link rel="stylesheet" type="text/css" href="style/index.css" media="screen" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="shortcut icon" href="images/main.png">
    <script src="scripts/d3.js"></script>

    <script src="scripts/d3v4.js"></script>
    <script src="scripts/jquery.js"></script>
    <script src="scripts/topojson.js"></script>
</head>

<body>
    <br />
    <header>
        <h1 id="title" style="text-align: center; width: 100%; font-family: Cambria, Cochin, Georgia, Times, 'Times New Roman', serif;">
            Movie Director Ratings
        </h1>
    </header>
    <br />
    <a type="button" href="countries.php" style="margin-left: 10px;margin-top: 10px;" type="button" class="btn btn-success">World Map</a>
    <br />
    <button type="button" style="margin-left: 10px;margin-top: 10px;" class="btn btn-secondary" id="btnReset" onclick="reset()">Reset</button>
    <div class="dropdown" style="margin-left:50px">
        <select onchange="checkMovies()" class="selectpicker custom-select" data-live-search="true" id="country1">
            <option disabled selected>Country 1</option>
        </select>
    </div>

    <div class="dropdown" style="margin-left:50px">
        <select onchange="checkMovies()" class="selectpicker custom-select" data-live-search="true" id="country2">
            <option disabled selected>Country 2</option>
        </select>
    </div>

    <div class="dropdown" style="margin-left:50px">
        <select onchange="checkMovies()" class="selectpicker custom-select" data-live-search="true" id="score">
            <option value="IMdb_score" selected>IMDb score</option>
            <option value="RT_score">RT score</option>
            <option value="Metascore">Metascore</option>
        </select>
    </div>
    <h3 id="graphTitle" style="text-align: center; width: 50%;">

    </h3>
    <div class="row">
        <div class="column1">
        </div>
        <div class="column2">
            <h3>
                Total Movie industry info
            </h3>
            <div class="moviesinfo" id="country" style="font-size:20px;margin-top:40px" id="country">Country: </div>
            <div class="moviesinfo" id="movies" style="font-size:20px;margin-top:20px" id="movies">Num of movies: </div>
            <div class="moviesinfo" id="directors" style="font-size:20px;margin-top:20px" id="directors">Num of directors: </div>
            <div class="moviesinfo" id="imdbscore" style="font-size:20px;margin-top:20px" id="imdbscore">Best Imdb: </div>
            <div class="moviesinfo" id="metascore" style="font-size:20px;margin-top:20px" id="metascore">Best Metascore: </div>
            <div class="moviesinfo" id="rtscore" style="font-size:20px;margin-top:20px" id="rtscore">Best RTscore: </div>
        </div>
    </div>

    <script>
        // Set margins and size of SVG
        var margin = {
                top: 40,
                right: 200,
                bottom: 30,
                left: 50
            },
            width = 900 - margin.left - margin.right,
            height = 500 - margin.top - margin.bottom;

        var firstCountry = "USA";
        var secondCountry = "USA";
        var movies = [];
        var movies1 = [];
        var movies2 = [];
        var chosenCountries = [];
        var chosenScore = "IMdb_score"

        //Get elements from selected ID
        var countryName = document.getElementById("country");
        var numOfMovies = document.getElementById("movies");
        var numOfDirectors = document.getElementById("directors");
        var bestImdb = document.getElementById("imdbscore");
        var bestMetascore = document.getElementById("metascore");
        var bestRTscore = document.getElementById("rtscore");

        //Parse date to given format
        var parseDate = d3.timeParse("%Y-%m-%d");

        var x = d3.scaleTime().range([0, width]);
        var y = d3.scaleLinear().range([height, 0]);

        //Create a line
        var line = d3.line()
            .x(function(d) {
                return x(d.Released);
            })
            .y(function(d) {
                return y(parseInt(d[chosenScore]));
            });

        //Create SVG form
        var svg = d3.select(".column1").append("svg")
            .attr("width", width + margin.left + margin.right)
            .attr("height", height + margin.top + margin.bottom)
            .append("g")
            .attr("transform", "translate(" + margin.left + "," + margin.top + ")");

        //Access movies JSON
        d3.json("movies.json", function(error, dataset) {

            //Filtering movies between 2004 and 2016
            movies = dataset.filter(function(item) {
                return parseInt(item.Year) > 2004 && parseInt(item.Year) < 2016;
            });

            var allCountries = [];

            //Extract unique country names
            dataset.map(function(item) {
                let items = item.Country.split(',')
                if (items.length > 1) {
                    items.forEach(element => {
                        if (allCountries.indexOf(element) == -1) {
                            allCountries.push(element);
                        }
                    });
                } else {
                    if (allCountries.indexOf(item.Country) == -1) {
                        allCountries.push(item.Country);
                    }
                }

            })

            //Add select options for Country 1 and Country2
            var countries1 = document.getElementById('country1');
            var countries2 = document.getElementById('country2');
            allCountries.sort((a, b) => a.localeCompare(b))
            allCountries.forEach(country => {
                var opt1 = document.createElement('option');
                var opt2 = document.createElement('option');
                opt1.value = country;
                opt1.innerHTML = country;
                opt2.value = country;
                opt2.innerHTML = country;
                countries1.appendChild(opt1);
                countries2.appendChild(opt2);
            });

            //Months array used to find index from given word which will represent selected month
            var months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];

            movies.forEach(function(d) {
                if (d.Released == null) {
                    d.Released = parseDate("1990-01-01")
                } else {
                    var parts = d.Released.split(' ');
                    if (months.indexOf(parts[1]) > 9) {
                        d.Released = parseDate(parts[2] + "-" + (months.indexOf(parts[1]) + 1) + "-" + parts[0]);
                    } else d.Released = parseDate(parts[2] + "-0" + (months.indexOf(parts[1]) + 1) + "-" + parts[0]);
                }
            });

            x.domain(d3.extent(movies, function(d) {
                return d.Released;
            }));

            y.domain([0,
                (d3.max(movies, function(d) {
                    return parseInt(d[chosenScore]);
                }) + 10)
            ]);

            const xAxisGrid = d3.axisBottom(x).tickSize(-height).tickFormat('').ticks(10);
            const yAxisGrid = d3.axisLeft(y).tickSize(-width).tickFormat('').ticks(10);

            // Create grids
            svg.append('g')
                .attr('class', 'x axis-grid')
                .attr('transform', 'translate(0,' + height + ')')
                .call(xAxisGrid);
            svg.append('g')
                .attr('class', 'y axis-grid')
                .call(yAxisGrid);

            //Add X Axis
            svg.append("g")
                .attr("transform", "translate(0," + height + ")")
                .call(d3.axisBottom(x));

            // Add the Y Axis
            svg.append("g")
                .call(d3.axisLeft(y));

            // Add X and Y Axis names
            svg.selectAll("mydots")
                .data([chosenScore])
                .enter()
                .append("text")
                .attr("x", 50)
                .attr("y", -25)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                .text(chosenScore)

            svg.selectAll("mydots")
                .data(["Year"])
                .enter()
                .append("text")
                .attr("x", width + 35)
                .attr("y", height - 20)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                .text(function(d) {
                    return d;
                })

        });

        function make_x_axis() {
            return d3.axisBottom()
                .scale(x)
                .ticks(5)
        }

        function make_y_axis() {
            return d3.axisLeft()
                .scale(y)
                .ticks(5)
        }

        function reset() {
            location.reload();
        }

        function drawGraph() {
            var keys = [];
            movies.forEach(function(element) {
                if (keys.indexOf(element.Country) == -1) {
                    keys.push(element.Country);
                }
            })

            //Update graph
            d3.selectAll("path.line").remove()
            d3.selectAll("mydots").remove()
            d3.selectAll("text").remove()

            var numbers = [Math.random() * 10, Math.random() * 10]

            //Colorscale
            var color = d3.scaleOrdinal(d3.schemeCategory10);

            var size = 20
            //Add legend dots and text
            var legend = svg.selectAll("mydots")
                .data(chosenCountries)
                .enter()
                .append("rect")
                .attr("x", width + 10)
                .attr("y", function(d, i) {
                    return 110 + i * (size + 15)
                })
                .attr("width", size)
                .attr("height", size)
                .style("fill", function(d, i) {
                    return color(numbers[i])
                })

            svg.selectAll("mydots")
                .data(chosenCountries)
                .enter()
                .append("text")
                .attr("x", width + 40)
                .attr("y", function(d, i) {
                    return 125 + i * (size + 15)
                })
                .text(function(item) {
                    return item.name;
                })

            svg.selectAll("mydots")
                .data([chosenScore])
                .enter()
                .append("text")
                .attr("x", 50)
                .attr("y", -25)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                .text(chosenScore)

            svg.selectAll("mydots")
                .data(["Year"])
                .enter()
                .append("text")
                .attr("x", width + 30)
                .attr("y", height - 20)
                .attr("dy", ".71em")
                .style("text-anchor", "end")
                .text(function(d) {
                    return d;
                })

            // Add the valueline path
            svg.append("path")
                .data([movies1])
                .attr("class", "line")
                .style("stroke", function() { // Add the colours dynamically
                    return color(numbers[0]);
                })
                .attr("d", line);

            // Add the valueline path
            svg.append("path")
                .data([movies2])
                .attr("class", "line")
                .style("stroke", function() { // Add the colours dynamically
                    return color(numbers[1]);
                })
                .attr("d", line);

            // Add the X Axis
            svg.append("g")
                .attr("transform", "translate(0," + height + ")")
                .call(d3.axisBottom(x));

            // Add the Y Axis
            svg.append("g")
                .call(d3.axisLeft(y));

        }

        function checkMovies() {
            //Get elements
            let countries1 = document.getElementById('country1');
            let countries2 = document.getElementById('country2');
            let score = document.getElementById('score');
            chosenCountries = [];

            //Filter countries from selected options and create Graph Title
            chosenScore = score.options[score.selectedIndex].value;
            let title = "";
            if (!countries1.options[countries1.selectedIndex].value.includes("Country 1")) {
                chosenCountries.push({
                    name: countries1.options[countries1.selectedIndex].value
                });
                movies1 = movies.filter(function(value) {
                    return value.Country.includes(countries1.options[countries1.selectedIndex].value) && value.Released != null
                })
                movies1.sort(function(a, b) {
                    return a.Released - b.Released;
                })
                title += countries1.options[countries1.selectedIndex].value;
                if (countries2.options[countries2.selectedIndex].value.includes("Country 2")) {
                    title += (" by " + chosenScore)
                }
            }
            if (!countries2.options[countries2.selectedIndex].value.includes("Country 2")) {
                chosenCountries.push({
                    name: countries2.options[countries2.selectedIndex].value
                });
                movies2 = movies.filter(function(value) {
                    return value.Country.includes(countries2.options[countries2.selectedIndex].value) && value.Released != null
                })
                movies2.sort(function(a, b) {
                    return a.Released - b.Released;
                })
                if (!countries1.options[countries1.selectedIndex].value.includes("Country 1")) {
                    title += (" vs " + countries2.options[countries2.selectedIndex].value + " by " + chosenScore)
                } else title += (countries2.options[countries2.selectedIndex].value + " by " + chosenScore);
            }
            //Set graph title
            let graphTitle = document.getElementById('graphTitle');
            graphTitle.innerHTML = title;
            drawGraph()
            setInfo()
        }

        function setInfo() {
            let countries1 = document.getElementById('country1');
            let countries2 = document.getElementById('country2');
            var firstCountryMovies = (movies1.concat(movies2)).filter(function(item) {
                return item.Country.includes(countries1.options[countries1.selectedIndex].value)
            })
            var secondCountryMovies = (movies1.concat(movies2)).filter(function(item) {
                return item.Country.includes(countries2.options[countries2.selectedIndex].value);
            })

            let imdb1 = getScore(firstCountryMovies, 1);
            let meta1 = getScore(firstCountryMovies, 2);
            let rt1 = getScore(firstCountryMovies, 3);
            let imdb2 = getScore(secondCountryMovies, 1);
            let meta2 = getScore(secondCountryMovies, 2);
            let rt2 = getScore(secondCountryMovies, 3);
            let dir1 = getScore(firstCountryMovies, 3);
            let dir2 = getScore(secondCountryMovies, 3);

            countryName.innerText = "Country: " + countries1.options[countries1.selectedIndex].value + " vs " + countries2.options[countries2.selectedIndex].value;
            numOfMovies.innerText = "Num of movies: " + firstCountryMovies.length + " vs " + secondCountryMovies.length;
            numOfDirectors.innerText = "Num of directors: " + dir1 + " vs " + dir2;
            bestImdb.innerText = "Best Imdb: " + imdb1 + " vs " + imdb2;
            bestMetascore.innerText = "Best Metascore: " + meta1 + " vs " + meta2;
            bestRTscore.innerText = "Best RTscore: " + rt1 + " vs " + rt2;
        }

        //Get score depending on given information
        function getScore(movies, number) {
            if (number == 1) {
                if (movies.length > 0) {
                    let score = Math.max.apply(null, movies.map(function(item) {
                        return parseInt(item.IMdb_score)
                    }));
                    return score;
                } else return "No score"
            } else if (number == 2) {
                if (movies.length > 0) {
                    let score = Math.max.apply(null, movies.map(function(item) {
                        return parseInt(item.Metascore)
                    }));
                    return score;
                } else return "No score"
            } else if (number == 3) {
                if (movies.length > 0) {
                    var rtScore = Math.max.apply(null, movies.map(function(item) {
                        return parseInt(item.RT_score)
                    }));
                    return rtScore;
                } else return "No score"
            } else {
                if (movies.length > 0) {
                    var countDirectors = [...new Set(movies.map(item => item.director_1))].length;
                } else return 0;
            }
        }
    </script>
</body>

</html>