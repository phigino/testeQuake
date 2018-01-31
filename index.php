<!DOCTYPE html>


<?php
include_once 'class.php';
$banco = new banco();
$result = $banco->Getresults();
$relatorio = $banco->getRelatorio();
?>
<html>
    <head>
        <title>Teste</title>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.bundle.min.js" integrity="sha384-feJI7QwhOS+hwpX2zkaeJQjeiwlhOP+SdQDqhgvvo1DsjtiSQByFdThsxO669S2D" crossorigin="anonymous"></script>
        <!-- get font ROBOTO -->
        <link href='http://fonts.googleapis.com/css?family=Roboto' rel='stylesheet' type='text/css'>

        <style>

            body{
                font-family: 'Roboto';
                background-color: #dadada;
            }
            ul {
                list-style-type: none;
                padding-left: 0px;
            }
            .cinza {
                background-color: #7c7b7b;
            }
            .preto {
                background-color: #646363;
            }
            .titulo{
                background: #f7a600;font-weight: bold;
            }
            p {
                margin-top: 20px;
                margin-bottom: 0;
            }
            img{
                max-height: 40px;
                float: left;
                border: transparent thin solid;
                padding: 5px;
                margin-top: .5rem;
                max-width: 40px;
            }
            h2 {

                margin-top: .5rem;
            }
            .form-control{
                background-color: #dadada;
                border: 1px solid white;
                border-radius: 0;
            }
            .btn-busca {
                color: #f7a600;
                background-color: #646363;
                border-color: #646363;
                width: 100%;
                padding: .375rem .75rem;
            }
            .busca{
                background-color: #646363;
            }
            .margin{
                margin-bottom: 20px;
            }
            .negativo{
                color: #f7a600;
            }

        </style>
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-md-center">
                <div class="col-6">

                    <label class="text-uppercase"><strong><h1>Ranking</h1></strong></label>
                </div>
            </div>
            <div class="row justify-content-md-center margin">
                <div class="col-4" style="padding-right: 0px; padding-left: 0px;"> 
                    <input type="text" id="myInput" onkeyup="myFunction()" name="busca" class="form-control" id="nome" placeholder="Busca por nome">

                </div>
                <div class="col-2 busca"><button type="submit" class="btn-busca">Buscar</button></div>

            </div>


            <div class="row text-dark justify-content-md-center">
                <div class="col-4 titulo"><h2>NAME</h2></div>
                <div class="col-2 titulo">

                    <img src="cavera.svg" alt="cavera" >

                    <h2 class="text-center">KILL</h2>
                </div>
            </div>

            <ul class="tabela-lista" id="myUL">
                <?php
                $cont = 0;
                foreach ($result as $value):

                    if ($cont == 0) {
                        $cor = 'cinza';
                        $cont = 1;
                    } else {
                        $cor = 'preto';
                        $cont = 0;
                    }
                    $mortes = array_sum($value['kill']);
                    $negativo = ($mortes < 0) ? 'negativo':'';
                    ?>
                    <li> 
                        <div class="row text-white justify-content-md-center">
                            <div class="col-4 <?= $cor ?>"><p><span><?= $value['nome'] ?></span></p></div>
                            <div class="col-2 <?= $cor ?>"><p class="text-center <?= $negativo ?>"><?= $mortes ?></p></div>
                        </div>
                    </li>
                <?php endforeach; ?>

            </ul>
            <div class="row justify-content-md-center">
                <div class="col-6">

                    <label class="text-uppercase"><strong><h1>Relatório</h1></strong></label>
                    <?php foreach ($relatorio as $value): ?>

                        <h2>Partida: <?= $value["id"] ?></h2>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Motivo</th>
                                    <th scope="col">Quantidade de Mortes</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($value['motivo'] as $key => $value1): ?>
                                    <tr>

                                        <td><?= $key ?></td>
                                        <td><?= $value1 ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr>

                                    <td>Total</td>
                                    <td><?= $value["total"] ?></td>
                                </tr>
                            </tbody>
                        </table>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <script>
            function myFunction() {
                var input, filter, ul, li, a, i;
                input = document.getElementById("myInput");
                filter = input.value.toUpperCase();
                ul = document.getElementById("myUL");
                li = ul.getElementsByTagName("li");
                for (i = 0; i < li.length; i++) {
                    span = li[i].getElementsByTagName("span")[0];
                    if (span.innerHTML.toUpperCase().indexOf(filter) > -1) {
                        li[i].style.display = "";
                    } else {
                        li[i].style.display = "none";

                    }
                }
            }
        </script>
    </body>
</html>
