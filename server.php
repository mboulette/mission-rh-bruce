<html>
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">

</head>
<body>

    <form>
        <textarea id="message"></textarea>
        <button id="btn_speak" type="button">Parler</button>
        <button id="btn_write" type="button">Écrire</button>
    </form>

    <video id="client_video" width="640" height="360" controls></video>

    <div id="output">
    </div>


    <script defer src="https://use.fontawesome.com/releases/v5.0.8/js/all.js"></script>
    <script type="text/javascript" src="javascript/jquery-3.3.1.min.js"></script>
    <script type="text/javascript" src="javascript/tether.min.js"></script>
    <script type="text/javascript" src="javascript/bootstrap.min.js"></script>
    <script type="text/javascript" src="javascript/simplepeer.min.js"></script>
    <script>

    $(function() {

        var audio_msg = new Audio('audio/bell.mp3');
        var audio_bell = new Audio('audio/bell-ringing-04.mp3');

        var unique_id = (new Date).getTime();
        var p = new SimplePeer({
            initiator: true,
            trickle: false,
            offerConstraints: { 
                mandatory: { 
                    OfferToReceiveAudio: true, 
                    OfferToReceiveVideo: true 
                }
            }
        });

        p.on('error', function (err) {
            console.log('error', err);
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
            //p.send(JSON.stringify({'action':'speech', 'msg':'Bonjour, je m\'appel BRUCE. Je suis votre borne robotisé universel de communication empathique.'}));
            p.send(JSON.stringify({'action':'speech', 'msg':'Comment puis-je vous aider?'}));
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
            
            $('#output').append('<div>'+data.msg+'</div>');
            if (data.action == 'bell') {
                audio_bell.play();
            } else {
                audio_msg.play();
            }

        });

        function connect_to_client() {

            $.get( "rooter.php", {'client' : true}, function( data ) {
                
                data = JSON.parse(data);

                console.log( data );
                if ( unique_id == data.id ) {
                    p.signal(data.client);
                } else {
                    setTimeout(function(){ connect_to_client(); }, 3000);
                }

            });

        }




        $(document).on('click', '#btn_speak', function(event){
            event.preventDefault();
            event.stopPropagation();

            p.send( JSON.stringify({'action':'speech', 'msg': $('#message').val()}) );
        });

        $(document).on('click', '#btn_write', function(event){
            event.preventDefault();
            event.stopPropagation();

            p.send( JSON.stringify({'action':'text', 'msg': $('#message').val()}) );
        });

    });

    </script>
</body>
</html>
    