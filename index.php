<?php
require __DIR__ . '\vendor\autoload.php';
include_once "nslookup.php";

$query = new NSLookup("example.com");
// echo "<pre>";
// print_r($query->nslookup());
// echo "</pre>";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>NSLookup</title>
    <style>
        *,
        *::after,
        *::before {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        html{
            font-size: 16px;
        }
        body {
            font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            position: relative;
        }
        header{
            background-color: lightslategrey;
            margin-bottom: 1rem;
            padding: 2rem 1rem;
        }
        header > h1,
        header > h2 {
            color: lightyellow;
            margin-bottom: 0.5 rem;
        }
        h2 {
            font-weight: 300;
        }
        h3 {
            margin-bottom: 1rem;
            text-transform: uppercase;
        }
        p{
            margin-bottom: 1rem;
        }
        main{
            padding: 1rem;
        }
        .code {
            background-color: #313131;
            color: white;
            font-family:'Courier New', Courier, monospace;
            font-size: 0.875rem;
            line-height: 1.618em;
            margin-bottom: 1em;
            padding: 1.618rem;
        }
        .code .num {
            color: lightgray;
        }
        .code .eq,
        .code .new {
            color: crimson;
        }
        .code .class {
            color: lightgreen;
        }
        .code .method {
            color: limegreen;
        }
    </style>
</head>
<body>
    <header>
        <h1>NSLookup Class</h1>
        <h2><em>Class to perform NSLookup on a domain</em><h2>
    </header>
    <main>
        <section id="usage">
            <article>
                <h3>Usage:</h3>
                <p>
                NSLookup is a PHP class which uses the NSLookup command to query for DNS records.  
                </p>
                <div class="code">
                    <code>
                        <span class="num">1.</span> $nslookup <span class="eq">=</span> <span class="new">new</span> <span class="class">NSLookup</span>("example.com");
                        <br>
                        <span class="num">2.</span> <span class="method">print_r(</span>$nslookup<span class="eq">-></span><span class="method">nslookup())</span>;
                    </code>
                </div>
                <div class="code">
                    <samp>&gt;&nbsp;
                    <?php
                        print_r($query->nslookup());
                    ?>
                    </samp>
                </div>
            </article>
        </section>
    </main>
    <footer>
    </footer>
</body>
</html>