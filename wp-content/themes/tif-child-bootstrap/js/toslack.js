var myJSONStr = 'payload= {
    "username": "SALE BOT",
    "icon_url": "example.com/img/icon.jpg",
    "attachments": [{
        "fallback": "This attachement isn't supported.",
        "title": "VALENTINE'S DAY OFFER",
        "color": "#9C1A22",
        "pretext": "Today's list of awesome offers picked for you",
        "author_name": "Preethi",
        "author_link": "http://www.hongkiat.com/blog/author/preethi/",
        "author_icon": "http://media05.hongkiat.com/author/preethi.jpg",
        "fields": [{
            "title": "Sites",
            "value": "_<http://www.amazon.com|Amazon>_\n_<http://www.ebay.com|Ebay>_",
            "short": true
        }, {
            "title": "Offer Code",
            "value": "UI90O22\n-",
            "short": true
        }],
        "mrkdwn_in": ["text", "fields"],
        "text": "Just click the site names and start buying. Get *extra reduction with the offer code*, if provided.",
        "thumb_url": "http://example.com/thumbnail.jpg"
    }]
}';

function postMessageToSlack(){
    var xmlhttp = new XMLHttpRequest(),
        webhook_url = url-you-saved-from-before,
        myJSONStr= json-string-from-above;
    xmlhttp.open('POST', webhook_url, false);
    xmlhttp.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xmlhttp.send(myJSONStr);
}
