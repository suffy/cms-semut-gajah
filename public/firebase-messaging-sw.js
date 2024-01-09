// Give the service worker access to Firebase Messaging.
// Note that you can only use Firebase Messaging here, other Firebase libraries
// are not available in the service worker.
// importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-app.js');
// importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-messaging.js');

// // Initialize the Firebase app in the service worker by passing in
// // your app's Firebase config object.
// // https://firebase.google.com/docs/web/setup#config-object
//     var firebaseConfig = {
//         apiKey: "AIzaSyAClo3NSMegblWg5Ule6Ly3n_abwbAeT6k",
//         authDomain: "antangin-mpm.firebaseapp.com",
//         projectId: "antangin-mpm",
//         storageBucket: "antangin-mpm.appspot.com",
//         messagingSenderId: "796841059848",
//         appId: "1:796841059848:web:7fcff42be82f328d6313bd",
//         measurementId: "G-4PJD9R8VTC"
//     };

// // Initialize Firebase
// firebase.initializeApp(firebaseConfig);

// Import the functions you need from the SDKs you need
// import { initializeApp } from "firebase/app";
// import { getAnalytics } from "firebase/analytics";
importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/7.23.0/firebase-messaging.js');
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
var firebaseConfig = {
  apiKey: "AIzaSyCxlcTS_NNGpTBrUQzh-TaHEQjf0QsQWMg",
  authDomain: "mpm-antangin.firebaseapp.com",
  projectId: "mpm-antangin",
  storageBucket: "mpm-antangin.appspot.com",
  messagingSenderId: "798332274625",
  appId: "1:798332274625:web:62354ece525df4bfb85140",
  measurementId: "G-HVNTWF4EQ2"
};

// Initialize Firebase
// const app = initializeApp(firebaseConfig);
// const analytics = getAnalytics(app);
firebase.initializeApp(firebaseConfig);

// Retrieve an instance of Firebase Messaging so that it can handle background
// messages.
const messaging = firebase.messaging();

messaging.setBackgroundMessageHandler(function(payload) {
    console.log('[firebase-messaging-sw.js] Received background message ', payload);
    // Customize notification here
    const {title, body} = payload.notification;
    const notificationOptions = {
        body,
    };

    return self.registration.showNotification(title,
        notificationOptions);
});
