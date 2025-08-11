importScripts('https://www.gstatic.com/firebasejs/8.2.0/firebase.js');
importScripts('https://www.gstatic.com/firebasejs/8.2.0/firebase-app.js');
importScripts('https://www.gstatic.com/firebasejs/8.2.0/firebase-messaging.js');

const config = {
  apiKey: "",
  authDomain: "",
  projectId: "",
  storageBucket: "",
  messagingSenderId: "",
  appId: "",
  measurementId: ""
};
firebase.initializeApp(config);
const fcm=firebase.messaging();
fcm.getToken({
    vapidKey:""
}).then((token)=>{
});



fcm.onBackgroundMessage((data)=>{
})