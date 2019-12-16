<?php header('Content-type: text/html; charset=utf-8'); ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<script type="text/javascript" src="../js/jquery.min.js"></script>
<script type="text/javascript" src="../js/d3.v3.min.js"></script>
</head>
<style>
    body {
      margin: auto;
      width: 600px;
    }

    text {
      font: 10px sans-serif;
    }

    .x-axis path, .x-axis line,
    .y-axis path, .y-axis line {
      fill: none;
      stroke: #000;
      shape-rendering: crispEdges;
    }

    .axis .grid-line {
        stroke: #000;
        stroke-opacity: 0.2;
    }

    .path-line {
      fill: none;
      stroke: url(#temperature-gradient);
      stroke-width: 2px;
    }
</style>

<body>

    <div id='sensor-graph'></div>

    <script type="text/javascript">

        // sensor data
        var rawData = [];
        // data array for D3js
        var data = [];
        // current sensor temperature data
        var lastTemperature = 0;

        // datetime parse function
        var parseDate = d3.time.format("%Y-%m-%d %H:%M:%S").parse;

        function show()  
        {
            // get sensor data async
            $.ajax({  
                url: "sensor_data_query.php",  
                cache: false,  
                success: function(response){
                    // parse responce into array
                    var rawData = jQuery.parseJSON(response);

                    // clear div
                    $('#sensor-graph').empty();
                    // clear D3js data array
                    data = [];

                    // prepare data[] for D3js
                    // each array row -> line[]
                    $.each(rawData, function(index, line) {
                        data.push({dt: parseDate(line[0]), tc: line[1]});
                    });

                    // current temperature = last t0C in the set
                    lastTemperature = rawData[rawData.length-1][1];

                    // draw svg graph using D3js
                    drawSensorGraph();
                }  
            });  
        }  
      
        $(document).ready(function(){  
            show();  
            setInterval('show()',60000); // 10 sec
        });

        //        
        function drawSensorGraph() {

            // margins
            var margin = 30;
            // graph area size
            var width = 600 - 2 * margin;
            var height = 400 - 2 * margin;

            // svg container
            var svg = d3.select("#sensor-graph").append("svg")
                .attr("class", "axis")
                .attr("width", width + 2 * margin)
                .attr("height", height + 2 * margin)
                .append("g")
                .attr("transform", "translate(" + margin + "," + margin + ")");

            // scale functions
            var xScale = d3.time.scale().range([0, width]);
            var yScale = d3.scale.linear().range([height, 0]);

            // function for drawing graph
            var pathLine = d3.svg.line()
                .interpolate("basis") // interpolation
                .x(function(d) { return xScale(d.dt); })
                .y(function(d) { return yScale(d.tc); });        

            // domain values for x,y axes
            xScale.domain([data[0].dt, data[data.length - 1].dt]);
            yScale.domain([16,36]);
            //yScale.domain(d3.extent(data, function(d) { return d.tc; }));
            
            // X,Y AXIS
            // create axis
            var xAxis = d3.svg.axis().scale(xScale).orient("bottom")
                        .ticks(6).tickFormat(d3.time.format('%d%b/%H:%M')); // ticks on X axis;
            var yAxis = d3.svg.axis().scale(yScale).orient("left");

            // draw Х axis
            svg.append("g")
                 .attr("class", "x-axis")
                 .attr("transform", "translate(0," + height + ")")
                 .call(xAxis);
            // draw Y axis
            svg.append("g")
                 .attr("class", "y-axis")
                 .call(yAxis)
                 .append("text")
                 .attr("transform", "rotate(-90)")
                 .attr("y", 6)
                 .attr("dy", ".71em")
                 .style("text-anchor", "end")
                 .text("Temperature (C)");

            // GRID
            // horisontal
            d3.selectAll("g.y-axis g.tick")
                .append("line") // add line
                .classed("grid-line", true) // add class
                .attr("x1", 0)
                .attr("y1", 0)
                .attr("x2", width)
                .attr("y2", 0);
            // vertical
            d3.selectAll("g.x-axis g.tick")
                .append("line") // add line
                .classed("grid-line", true) // add class
                .attr("x1", 0)
                .attr("y1", 0)
                .attr("x2", 0)
                .attr("y2", (-height));

            // PATH
            // add gradient
            svg.append("linearGradient")
                .attr("id", "temperature-gradient")
                .attr("gradientUnits", "userSpaceOnUse")
                .attr("x1", 0).attr("y1", yScale(20.0)) // blue-yellow edge
                .attr("x2", 0).attr("y2", yScale(23.0)) // yellow-red edge
                .selectAll("stop")
                .data([{offset: "0%", color: "steelblue"},
                       {offset: "50%", color: "yellow"},
                       {offset: "100%", color: "red"}])
                .enter().append("stop")
                .attr("offset", function(d) { return d.offset; })
                .attr("stop-color", function(d) { return d.color; });

            // draw path
            svg.append("path")
                .datum(data)
                .attr("class", "path-line")
                .attr("d", pathLine);

            
            // CURRENT TEMPERATURE TEXT
            svg.append("g").append("text")
                .attr("x", 25)
                .attr("y", 50)
                .attr("text-anchor", "start")
                .style("font-size", "48px")
                .text(lastTemperature + "ºC");    
        }

    </script>

</body>
</html>
