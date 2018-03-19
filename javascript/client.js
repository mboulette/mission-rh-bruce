

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

    var voice_option = "bruce";
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

            $.post( "https://www.mission-rh.org/bruce/rooter.php", {'id' : server_id, 'client' : JSON.stringify(data)}, function( data ) {
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

            setTimeout(function(){ peer(window.stream); }, 5000);
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
            } else if (data.action == 'wait') {
                
                system_write(data.msg);

                $('button, a').prop('disabled', true);
                $('#waiting svg').removeClass('d-none');

                setTimeout(function(){
                    $('button, a').prop('disabled', false);
                    $('#waiting svg').addClass('d-none'); 
                }, 10000);

            } else {                
                server_write(data.msg);
            }

        });


        $.get( "https://www.mission-rh.org/bruce/rooter.php", {'server' : true}, function( data ) {
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
            if (event.which == 13 && !event.shiftKey) {
                event.preventDefault();
                event.stopPropagation();                

                $('form#frm_send').submit();
            }
        });

       $(document).on('submit', '#frm_send', function(event){
            event.preventDefault();
            event.stopPropagation();

            if ( $('#btn_submit').prop('disabled') ) return false;

            p.send( JSON.stringify({'action':'text', 'msg': $('#message').val()}) );

            client_write($('#message').val());
            $('#message').val('');
        });

       $(document).on('click', '#btn_bell', function(event){
            event.preventDefault();
            event.stopPropagation();

            p.send( JSON.stringify({'action':'bell', 'msg': '<i class="fas fa-bell"></i> RING... RING...'}) );
            system_write('<i class="fas fa-bell"></i> RING... RING...');

        });

       $(document).on('click', '.voice-choice', function(event){
            event.preventDefault();
            //event.stopPropagation();

            $('.voice-choice.active').removeClass('active');
            $(this).addClass('active');
            voice_option = $(this).data('voice');
       });

    });
