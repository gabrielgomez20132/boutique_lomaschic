// Concatena los assets igual que webpack.mix.js, sin dependencias.
// Uso: node build-assets.js   (o: npm run assets)
const fs = require('fs');

const bundles = {
  'public/js/app.js': [
    'resources/assets/js/jquery.min.js',
    'resources/assets/js/bootstrap.min.js',
    'resources/assets/js/vue.min.js',
    'resources/assets/js/axios.min.js',
    'resources/assets/js/toastr.min.js',
    'resources/assets/js/moment.min.js',
    'resources/assets/js/sb-admin-2.js',
    'resources/assets/js/metisMenu.min.js',
    'resources/assets/js/app.js',
  ],
  'public/css/app.css': [
    'resources/assets/css/app.css',
    'resources/assets/css/toastr.min.css',
    'resources/assets/css/metisMenu.min.css',
    'resources/assets/css/sb-admin-2.css',
    'resources/assets/css/open-iconic-bootstrap.css',
  ],
};

for (const [out, files] of Object.entries(bundles)) {
  const sep = out.endsWith('.js') ? ';\n' : '\n';
  const content = files.map(f => fs.readFileSync(f, 'utf8')).join(sep);
  fs.writeFileSync(out, content);
  console.log(`OK ${out} (${(content.length / 1024).toFixed(0)} KB)`);
}
