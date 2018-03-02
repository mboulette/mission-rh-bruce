<html>
<body>

    <form>
        <textarea id="message"></textarea>
        <button id="btn_submit" type="submit">submit</button>
    </form>

    <div id="output">
    </div>

    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.8/jquery.min.js"></script>
    <script type="text/javascript" src="javascript/chunkify.js"></script>
    <script src="javascript/simplepeer.min.js"></script>
    <script>


    var server_id = 0;
    var p;

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
            p.send('Le client est connecté!');
        });

        p.on('data', function (data) {
            console.log(JSON.parse(data));
            data = JSON.parse(data);

            if (data.type == 'speech') {
                read(data.msg);
            } else {
                $('#output').append('<div>'+data.msg+'</div>');
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

       $(document).on('click', '#btn_submit', function(event){
            event.preventDefault();
            event.stopPropagation();

            console.log( p );

            p.send( $('#message').val() );
        });

    
    });

    </script>
</body>
</html>