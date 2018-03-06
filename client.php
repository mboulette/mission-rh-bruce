<html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <title>Console BRUCE</title>

    <style>

        *:focus {
            outline: none;
            box-shadow: none;
        }

        .band {
            background-color: #3897e0;
            border-radius:3px 3px 0 0;
        }
        
        .my-sidebar {
            -ms-flex: 0 0 400px;
            flex: 0 0 400px;
            background-color: black; 
        }

        .output {
            vertical-align: top;
            background-color: #efefef;
        }

        .sidebar {
            width:400px;
            vertical-align: top;
            background-color: #000;
            text-align: center;
        }

        .chatbar {
            height:100px;
            vertical-align: middle;
            background-color: #efefef;
        }

        #visualiser {
            margin-left:25px;
        }

        textarea {
            resize: none;
        }

        form {
            margin: 0 10px 0 10px;
        }

        #output {
            height: 100%;
            width: 100%;
            overflow-y: scroll;
            overflow-x: auto;
            margin-top: 10px;
        }
        
    </style>

</head>
<body>


    <table style="width:100%; height:100%;">
        <tr>
            <td class="output">
                <div id="output" class="container">

                    <div class="row">
                        <div class="col-10"><div class="alert alert-primary">Vous êtes connecté!</div></div>
                        <div class="col-2"><div class="alert alert-light"><strong>BRUCE</strong></div></div>
                    </div>

                    <div class="row">
                        <div class="col-2"><div class="alert alert-light"><strong>VOUS</strong></div></div>
                        <div class="col-10"><div class="alert alert-secondary">Bonjour!!</div></div>
                    </div>


                </div>

            </td>
            <td rowspan='2' class="sidebar">
                <img src="img/ai_product_v2.gif">
                <div id="visualiser"></div>
            </td>
        </tr>
        <tr>
            <td class="chatbar">

                        <form id="frm_send" class="">

                                <div class="input-group">
                                    <textarea id="message" class="form-control"></textarea>
                                    <div class="input-group-append">
                                        <button id="btn_submit" class="btn btn-primary btn-lg" type="submit">Envoyer</button>
                                        <button id="btn_bell" class="btn btn-info btn-lg" type="button"><i class="fas fa-bell"></i></button>
                                    </div>
                                </div>



                        </form>
            </td>
        </tr>
    </table>

    <script type="text/javascript" src="javascript/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="javascript/tether.min.js"></script>
    <script type="text/javascript" src="javascript/bootstrap.min.js"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js"></script>
    <script type="text/javascript" src="javascript/spectrum.js"></script>
    <script type="text/javascript" src="javascript/chunkify.js"></script>
    <script type="text/javascript" src="javascript/simplepeer.min.js"></script>
    <script>

    var sp = makeSpectrum('visualiser', 350, 100, 20);
    var server_id = 0;
    var p;

    var server_write = function(msg) {
        $('#output').append('<div class="alert alert-primary">'+msg+'</div>');
    }

    var client_write = function(msg) {
        $('#output').append('<div class="alert alert-secondary">'+msg+'</div>');
    }

    var peer = function (stream) {

        p = new SimplePeer({
            initiator: false, 
            trickle: false,
            stream: stream,
            answerConstraints: { 
                mandatory: { 
                    OfferToReceiveAudio: false, 
                    OfferToReceiveVideo: false 
                } 
            }
        });

        p.on('error', function (err) {
            console.log('error', err)
        });

        p.on('signal', function (data) {
            console.log('SIGNAL', data);

            $.post( "rooter.php", {'id' : server_id, 'client' : JSON.stringify(data)}, function( data ) {
                console.log( data );
            });

        });

        p.on('connect', function () {
            console.log('CONNECT');
            p.send(JSON.stringify({'action':'bell', 'msg': 'Le client est connecté!'}));
        });

        p.on('data', function (data) {
            console.log(JSON.parse(data));
            data = JSON.parse(data);

            if (data.msg == '') return false;

            if (data.action == 'speech') {
                
                sp.fadeIn();
                read(data.msg, function(){
                    
                    console.log('test');
                    sp.fadeOut();
                });
            } else {
                server_write(data.msg);
                $('#output').scrollTop( $('#output')[0].scrollHeight );
            }

        });


        $.get( "rooter.php", {'server' : true}, function( data ) {
            data = JSON.parse(data);

            console.log( data );
            server_id = data.id
            p.signal(data.server);
        });
       
    }


    $(function() {
    
        navigator.mediaDevices = navigator.mediaDevices || ((navigator.mozGetUserMedia || navigator.webkitGetUserMedia) ? {
            getUserMedia: function(c) {
                return new Promise(function(y, n) {
                    (navigator.mozGetUserMedia || navigator.webkitGetUserMedia).call(navigator, c, y, n);
             });
           }
        } : null);

        navigator.mediaDevices.getUserMedia({ audio: true, video: { width: 1280, height: 720 } })
        .then(function(stream) {     

            peer(stream);
        })
        .catch(function(err) {
            console.log(err.name + ": " + err.message);
        });


        $(document).on('keypress', '#message', function (event) {
            if (event.which == 13 && event.shiftKey) {
                event.preventDefault();
                event.stopPropagation();                

                $('form#frm_send').submit();
            }
        });

       $(document).on('submit', '#frm_send', function(event){
            event.preventDefault();
            event.stopPropagation();

            p.send( JSON.stringify({'action':'text', 'msg': $('#message').val()}) );

            client_write($('#message').val());
            $('#output').scrollTop( $('#output')[0].scrollHeight );
            $('#message').val('');
        });

       $(document).on('click', '#btn_bell', function(event){
            event.preventDefault();
            event.stopPropagation();

            p.send( JSON.stringify({'action':'bell', 'msg': 'Ring...'}) );
            client_write('RING... RING...');
            $('#output').scrollTop( $('#output')[0].scrollHeight );
        });

    });

    </script>
</body>
</html>