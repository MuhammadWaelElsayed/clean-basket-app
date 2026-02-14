<!--begin::Footer-->
<div id="kt_app_footer" class="app-footer bg-base text-white">
    <!--begin::Footer container-->
    <div class="app-container container-fluid d-flex flex-column flex-md-row flex-center flex-md-stack py-3">
        <!--begin::Copyright-->
        <div class="text-dark order-2 order-md-1">
            <span class="text-white fw-semibold me-1">{{date('Y')}}&copy;</span>
            <a href="#" target="_blank" class=" text-white">{{env('APP_NAME')}}</a>
        </div>
        <!--end::Copyright-->
        <!--begin::Menu-->
        <ul class="menu menu-gray-600 menu-hover-primary fw-semibold order-1  ">
            <li class="menu-item"> 
                <a href="{{ url('/#about') }}" class="menu-link text-white px-2">{{__("About")}}</a>
            </li>
            {{-- <li class="menu-item"> 
                <a href="{{ url('vendor/support') }}" class="menu-link text-white px-2">{{__("Support")}}</a>
            </li> --}}
        </ul>
        <!--end::Menu-->
    </div>
    <!--end::Footer container-->
</div>

{{-- FCM  --}}
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js" data-navigate-once></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js" data-navigate-once></script>
<script>
    
    // Your Firebase configuration
    // const firebaseConfig = {
    //     apiKey: "AIzaSyA3Gzei323NkC3SlIyOMtxttMbz9AgpH-0",
    //     authDomain: "legal-platform-6e119.firebaseapp.com",
    //     projectId: "legal-platform-6e119",
    //     storageBucket: "legal-platform-6e119.appspot.com",
    //     messagingSenderId: "1056379556496",
    //     appId: "1:1056379556496:web:4a4a221e24486c98357e15",
    //     measurementId: "G-231C8BQRL3"
    // };
    // // Initialize Firebase
    // firebase.initializeApp(firebaseConfig);
    // const messaging = firebase.messaging();

    // messaging.onMessage(function(payload) {
    //     const notTitle = payload.notification.title;
    //     const notBody = payload.notification.body;
    //     console.log(payload);
    //     const noteOptions = {
    //         body: notBody,
    //         icon: payload.notification.icon,
    //     };
    //     // Play sound
    //     setInterval(function(){
    //         const audio = new Audio("{{ asset('media/new-order.mp3') }}"); // Replace 'path_to_your_sound_file.mp3' with your sound file path
    //         audio.play();
    //     }, 2000);

    //     new Notification(notTitle, noteOptions);
    //     Swal.fire({ title: notTitle, text: notBody, icon: "warning",
    //     buttonsStyling: !1, confirmButtonText: "Ok, got it!",
    //     customClass: { confirmButton: "btn btn-base" } })
    //     .then((result) => {
    //         if (result.isConfirmed) {
    //             // Redirect to '/vendors/orders'
    //             window.location.href = payload.data.link;
    //         }
    //     });
    // });


    

</script>