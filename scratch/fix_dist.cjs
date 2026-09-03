const fs = require('fs');

let html = fs.readFileSync('dist/index.html', 'utf8');

// Replace all local origins with relative ./
html = html.split('http://192.168.1.176:8000/').join('./');
html = html.split('http://192.168.1.176:8000').join('./');
html = html.split('http://foodcart-management.test/').join('./');
html = html.split('http://foodcart-management.test').join('./');

// Add base tag for GitHub Pages
if (!html.includes('<base href="/foodcart-management/">')) {
    html = html.replace('<meta charset="utf-8">', '<meta charset="utf-8">\n    <base href="/foodcart-management/">');
}

fs.writeFileSync('dist/index.html', html, 'utf8');
console.log('Successfully updated dist/index.html with relative paths and base tag!');
