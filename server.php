<html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

    <title>Console BRUCE - Rh-PATAF</title>

    <style>

        .band {
            background-color: #3897e0;
            border-radius:3px 3px 0 0;
        }

        .output {
            vertical-align: top;
            background-color: #efefef;
        }

        .sidebar {
            width:600px;
            vertical-align: top;
            background-color: #fff;
            text-align: center;
        }

        .chatbar {
            height:100px;
            vertical-align: middle;
            background-color: #efefef;
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

        .menu {
            height: 54px;
            padding: 0;
            background-color: #000;
        }

        sub {
            font-size: 50%;
        }

        .btn-sm {
            margin: 5px;
        }

        button span.del {
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

        <div class="row msg-speek">
            <div class="col-2"><div class="alert alert-light text-center"><strong><i class="fas fa-volume-up"></i></strong></div></div>
            <div class="col-10"><div class="alert alert-secondary content"></div></div>
        </div>

        <div class="row msg-write">
            <div class="col-2"><div class="alert alert-light text-center"><strong><i class="fas fa-pencil-alt"></i></strong></div></div>
            <div class="col-10"><div class="alert alert-secondary content"></div></div>
        </div>

        <div class="row msg-client">
            <div class="col-2"><div class="alert alert-light text-center"><strong>CLIENT</strong></div></div>
            <div class="col-1">&nbsp;</div>
            <div class="col-9"><div class="alert alert-primary content"></div></div>
        </div>
    </div>

    <table style="width:100%; height:100%;">
            <tr>
                <td colspan=2 class="menu">
                    <nav class="navbar navbar-dark bg-dark navbar-expand">
                        <!-- Navbar content -->
                        <a class="navbar-brand" href="https://www.mission-rh.org">
                            <img src="/bruce/img/chat-2-icon-32.png" width="32" height="32">
                        </a>

                        <span class="navbar-brand">B.R.U.C.E <sub>v1.0</sub> - SERVEUR</span>
                    </nav>
                </td>
            </tr>
            <tr>
                <td class="output">
                    <div id="output" class="container-fluid">
                    </div>
                </td>
                <td class="sidebar" rowspan="2">
                    <video id="client_video" width="600" height="338" controls></video>

                    <h3>Réponses rapides</h3>
                    <div id="quick-list">
                    <?php
                    $json_file = file_get_contents(__DIR__."/preset.json");

                    $json_array = json_decode($json_file, true);

                    foreach ($json_array as $short => $long) {
                        echo '<button type="button" class="quick-answer btn btn-secondary btn-sm" data-text="'.$long.'">'.$short.'</button>';
                    }
                    ?>
                    </div>

                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-rapid-answer">Ajouter une réponse rapide</button>

                </td>
            </tr>
            <tr>
                <td class="chatbar">
                    <form id="frm_send" class="">
                        <div class="input-group">
                            <textarea id="message" class="form-control"></textarea>
                            <div class="input-group-append">
                                <button id="btn_speak" class="btn btn-primary btn-lg" type="submit">Envoyer</button>
                                <button id="btn_write" class="btn btn-info btn-lg" type="button"><i class="fas fa-pencil-alt"></i></button>
                                <button id="btn_wait" class="btn btn-danger btn-lg" type="button"><i class="fas fa-hourglass-half"></i></button>
                            </div>
                        </div>
                    </form>
                </td>
            </tr>
        </table>

        <div id="add-rapid-answer" class="modal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <form>
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Ajouter une réponse rapide</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        </div>
                        <div class="modal-body">

                            <div class="form-group">
                                <label for="#quick-name">Nom</label>
                                <input type="text" class="form-control" id="quick-name" maxlength="30" placeholder="Ajouter un nom court pour la retrouver facilement">
                                
                            </div>

                            <div class="form-group">
                                <label for="#quick-body">Réponse</label>
                                <textarea class="form-control" id="quick-body" placeholder="Le contenue de la réponse ici"></textarea>
                                
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" id="quick-save">Enregistrer</button>
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Annuler</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <script type="text/javascript" src="javascript/moment-with-locales.min.js"></script>
    <script type="text/javascript" src="javascript/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="javascript/fontawesome.min.js"></script>
    <script type="text/javascript" src="javascript/tether.min.js"></script>
    <script type="text/javascript" src="javascript/bootstrap.min.js"></script>
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


    $(function() {

        moment.locale('fr-ca');

        var audio_msg = new Audio('audio/bell.mp3');
        var audio_bell = new Audio('audio/bell-ringing-04.mp3');

        var unique_id = (new Date).getTime();

        var config = {
            initiator: true,
            trickle: false,
            offerConstraints: { 
                mandatory: { 
                    OfferToReceiveAudio: true, 
                    OfferToReceiveVideo: true 
                }
            }
        }

        var connect_to_client = function() {

            $.get( "rooter.php", {'client' : true}, function( data ) {
                
                data = JSON.parse(data);
                console.log( data );
                system_write('Recherche du client...');

                if ( unique_id == data.id ) {
                    peer.signal(data.client);
                } else {
                    setTimeout(function(){ connect_to_client(); }, 3000);
                }

            });

        }

        var create_peer = function(config) {
            var p = new SimplePeer(config);

            p.on('error', function (err) {
                console.log('error', err);
                error_write('Erreur:' + err);
            });

            p.on('signal', function (data) {
                console.log('SIGNAL', data);

                $.post( "rooter.php", {'id' : unique_id, 'server' : JSON.stringify(data)}, function( data ) {
                    console.log( data );
                });

                setTimeout(function(){ connect_to_client(); }, 3000);

            });

            p.on('connect', function () {
                console.log('CONNECT');
                system_write('Connexion en cours...');

                p.send(JSON.stringify({'action':'text', 'msg':'Bonjour, je m\'appel BRUCE. Je suis votre Borne Robotisé Universel de Communication Empathique.<br>Vous pouvez m\'écrire, ou me parler directement.<br>Comment puis-je vous aider?'}));
                p.send(JSON.stringify({'action':'speech', 'msg':'Bonjour, Comment puis-je vous aider?'}));
            });

            p.on('close', function () {
                console.log('DISCONNECT');
                error_write('Vous avez été déconecté!... Tentative de reconnexion');

                unique_id = (new Date).getTime();
                peer = create_peer(config);
            });


            p.on('stream', function (stream) {           
                console.log(stream);

                var video = document.querySelector('video');
                video.src = window.URL.createObjectURL(stream);
                video.onloadedmetadata = function(e) {
                    video.play();
                };
            });


            p.on('data', function (data) {
                data = JSON.parse(data);

                if (data.action == 'bell') {
                    system_write(data.msg);
                    audio_bell.play();
                } else {
                    client_write(data.msg);
                    audio_msg.play();
                }

            });

            return p;
        }

        var peer = create_peer(config);

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

        var server_speek = function(msg) {
            var template = $('#template .msg-speek').clone();
            chat(template, msg);
        }

        var server_write = function(msg) {
            var template = $('#template .msg-write').clone();
            chat(template, msg);

            audio_msg.play();
        }

        var client_write = function(msg) {
            var template = $('#template .msg-client').clone();
            chat(template, msg);
        }

        var add_quick_answer = function( new_answer ) {
            $('#quick-list').append('<button type="button" id="'+new_answer.id+'" class="quick-answer btn btn-dark btn-sm">'+new_answer.name+'&nbsp; <span class="del" data-id="'+new_answer.id+'">&times;</span></button>');
            $('#'+new_answer.id).data('text', new_answer.body);
        }

        var ls = [];
        if (localStorage.getItem('quick-answer')) {
            ls = JSON.parse(localStorage.getItem('quick-answer'));    
        }
        if (ls) {
            for (var i = 0; i < ls.length; i++){
                add_quick_answer( ls[i] );
            }
        }

        $(document).on('keypress', '#message', function (event) {
            if (event.which == 13 && !event.shiftKey) {
                event.preventDefault();
                event.stopPropagation();                

                $('form#frm_send').submit();
            }
        });

       $(document).on('submit', '#frm_send', function(event){
            event.preventDefault();
            event.stopPropagation();

            if ( $('#message').val() == '' ) return false;

            peer.send( JSON.stringify({'action':'speech', 'msg': $('#message').val()}) );

            server_speek($('#message').val());
            $('#message').val('');
        });

        $(document).on('click', '#btn_write', function(event){
            event.preventDefault();
            event.stopPropagation();

            if ( $('#message').val() == '' ) return false;            

            peer.send( JSON.stringify({'action':'text', 'msg': $('#message').val()}) );
            
            server_write($('#message').val());
            $('#message').val('');
        });

        $(document).on('click', '#btn_wait', function(event){
            event.preventDefault();
            event.stopPropagation();

            peer.send( JSON.stringify({'action':'wait', 'msg': '<i class="fas fa-hourglass-half"></i> Veuillez patienter...'}) );
            system_write('<i class="fas fa-hourglass-half"></i> Veuillez patienter...');
        }); 

        $(document).on('click', '.quick-answer', function(event){
            event.preventDefault();
            event.stopPropagation();

            peer.send( JSON.stringify({'action':'speech', 'msg': $(this).data('text')}) );
            server_speek($(this).data('text'));
        });

        $(document).on('click', '#quick-save', function(event){
            event.preventDefault();
            event.stopPropagation();

            new_answer = {'id' : new Date().getTime(), 'name' : $('#quick-name').val(), 'body' : $('#quick-body').val()};

            ls.push(new_answer);
            localStorage.setItem('quick-answer', JSON.stringify(ls));

            add_quick_answer( new_answer );
            $('#add-rapid-answer').modal('hide')

            $('#quick-name').val('');
            $('#quick-body').val('');
        });

        $(document).on('click', '.del', function(event){
            event.preventDefault();
            event.stopPropagation();

            if (ls) {

                new_list = [];

                for (var i = 0; i < ls.length; i++){
                    if (ls[i].id != $(this).data('id') ) {
                        new_list.push(ls[i]);
                    }
                }

                ls = new_list;
                localStorage.setItem('quick-answer', JSON.stringify(ls));
            }

            $(this).parent().remove();
        });
               

    });

    </script>
</body>
</html>
    