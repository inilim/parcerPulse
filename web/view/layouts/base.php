<!DOCTYPE html><html lang="ru">
<head>
   <meta charset="UTF-8">
   <link href="https://fonts.googleapis.com/css?family=Montserrat:300,400,700" rel="stylesheet">
   <meta name="viewport" content="width=device-width, initial-scale=1">
   <link rel="stylesheet" href="/style.css">
   <title>Pulse network analysis</title>
</head>

<body>
  <div class="wrapper">
    <h1>Pulse network analysis</h1>
    <div class="cols">
      <div class="col" style="display: none;" ontouchstart="this.classList.toggle('hover');">
        <div class="container">
          <div class="front" style="">
            <div class="inner">
              <p></p>
              <span></span>
            </div>
          </div>
          <div class="back">
            <div class="inner">
              <p></p>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="wrapper__buttons">
      <div id="prev" class="button_base b05_3d_roll">
        <div>Prev</div>
        <div>Prev</div>
      </div>
      <div id="next" class="button_base b05_3d_roll">
        <div>Next</div>
        <div>Next</div>
      </div>
    </div>
  </div>
<?php $this->render('js', [
   'json' => $json
]); ?>
</body>
</html>



