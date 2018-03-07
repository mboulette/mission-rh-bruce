<html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <title>Console BRUCE - Rh-PATAF</title>

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

        .bruce_avatar {
            background-image:url(img/ai_product_v2.gif);
            width:400px;
            height:300px;
            padding-top:175px;
        }

        .info {
            color: #bbb;
            padding: 25px;
        }

        #visualiser {
            margin-left: 25px;
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

        .row small {
            font-size: 60%;
        }

        small {
            font-size: 80%;
        }

        sub {
            font-size: 50%;
        }
        
    </style>

</head>
<body>

    <div id="template" class="d-none">

        <div class="row msg-system">
            <div class="col-12"><div class="alert alert-success content"></div></div>
        </div>

        <div class="row msg-error">
            <div class="col-12"><div class="alert alert-danger content"></div></div>
        </div>

        <div class="row msg-server">
            <div class="col-2"><div class="alert alert-light text-center"><strong>BRUCE</strong></div></div>
            <div class="col-1">&nbsp;</div>
            <div class="col-9"><div class="alert alert-primary content"></div></div>
        </div>

        <div class="row msg-client">
            <div class="col-2"><div class="alert alert-light text-center"><strong>VOUS</strong></div></div>
            <div class="col-10"><div class="alert alert-secondary content"></div></div>
        </div>
    </div>


    <table style="width:100%; height:100%;">
        <tr>
            <td class="output">
                <div id="output" class="container-fluid">
                </div>
            </td>
            <td rowspan='2' class="sidebar">
                <div class="bruce_avatar">
                    <div id="visualiser"></div>
                </div>

                <div>&nbsp;</div>

                <h1>B.R.U.C.E <sub>v1.0</sub></h1>

                <div class="info">
                    Commencé en interpelant Bruce ou en cliquant sur la sonnette pour le sortir d'une veille prolongé.               
                </div>
                <small>Help - Aide - Ayuda - 帮助 - मदद - Hilfe</small>

                <img src="img/home-header-logo.png" width="200" style="position: absolute; bottom: 25px; right: 100px;">
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

    <script type="text/javascript" src="javascript/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="javascript/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="javascript/tether.min.js"></script>
    <script type="text/javascript" src="javascript/bootstrap.min.js"></script>
    <script type="text/javascript" src="javascript/fontawesome.min.js"></script>
    <script type="text/javascript" src="javascript/spectrum.js"></script>
    <script type="text/javascript" src="javascript/chunkify.js"></script>
    <script type="text/javascript" src="javascript/simplepeer.min.js"></script>
    <script>

    String.prototype.nl2br = function() {
        return this.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1'+ '<br>' +'$2');
    }

    String.prototype.linkify = function() {

        if ( this.includes('<') || this.includes('>') ) return this;

        // http://, https://, ftp://
        var urlPattern = /\b(?:https?|ftp):\/\/[a-z0-9-+&@#\/%?=~_|!:,.;]*[a-z0-9-+&@#\/%=~_|]/gim;

        // www. sans http:// or https://
        var pseudoUrlPattern = /(^|[^\/])(www\.[\S]+(\b|$))/gim;

        // Email addresses
        var emailAddressPattern = /[\w.]+@[a-zA-Z_-]+?(?:\.[a-zA-Z]{2,6})+/gim;

        return this
            .replace(urlPattern, '<a target="_blank" href="$&">$&</a>')
            .replace(pseudoUrlPattern, '$1<a target="_blank" href="http://$2">$2</a>')
            .replace(emailAddressPattern, '<a target="_blank" href="mailto:$&">$&</a>');
    };

    moment.locale('fr-ca');

    var audio_msg = new Audio('audio/bell.mp3');
    var sp = makeSpectrum('visualiser', 350, 100, 20);
    var server_id = 0;
    var p;

    var chat = function(template, msg) {
        var time = "<small>"+moment().format('LLLL')+'</small><br>';


        template.find('.content').html( time + msg.linkify().nl2br() );
        $('#output').append(template);
        $('#output').scrollTop( $('#output')[0].scrollHeight );
    }

    var error_write = function(msg) {
        var template = $('#template .msg-error').clone();
        chat(template, msg);
    }

    var system_write = function(msg) {
        var template = $('#template .msg-system').clone();
        chat(template, msg);
    }

    var server_write = function(msg) {
        var template = $('#template .msg-server').clone();
        chat(template, msg);

        audio_msg.play();
    }

    var client_write = function(msg) {
        var template = $('#template .msg-client').clone();
        chat(template, msg);
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
            error_write('Erreur:' + err);
        });

        p.on('signal', function (data) {
            console.log('SIGNAL', data);
            system_write('Recherche du serveur...');

            $.post( "rooter.php", {'id' : server_id, 'client' : JSON.stringify(data)}, function( data ) {
                console.log( data );
            });

        });

        p.on('connect', function () {
            console.log('CONNECT');
            system_write('Vous êtes maintenant connecté à BRUCE');

            p.send(JSON.stringify({'action':'bell', 'msg': 'Le client est connecté!'}));
        });

        p.on('close', function () {
            console.log('DISCONNECT');
            error_write('Vous avez été déconecté!... Tentative de reconnexion');

            setTimeout(function(){ peer(window.stream); }, 3000);
        });

        p.on('data', function (data) {
            console.log(JSON.parse(data));
            data = JSON.parse(data);

            if (data.msg == '') return false;

            if (data.action == 'speech') {
                
                sp.fadeIn();
                read(data.msg, function(){
                    sp.fadeOut();
                });
            } else {
                server_write(data.msg);
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
            window.stream = stream;
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
            $('#message').val('');
        });

       $(document).on('click', '#btn_bell', function(event){
            event.preventDefault();
            event.stopPropagation();

            p.send( JSON.stringify({'action':'bell', 'msg': 'Ring...'}) );
            system_write('<i class="fas fa-bell"></i> RING... RING...');

        });

    });

    </script>
</body>
</html>