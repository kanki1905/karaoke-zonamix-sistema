// Verificador de contraste WCAG: lee los tokens de style.css y comprueba pares.
const fs=require('fs');
const css=fs.readFileSync(process.argv[2],'utf8');
function block(sel){const i=css.indexOf(sel+' {');if(i<0)throw sel;let d=0,j=css.indexOf('{',i);const st=j;for(;j<css.length;j++){if(css[j]=='{')d++;if(css[j]=='}'){d--;if(!d)break}}return css.slice(st+1,j)}
function parse(b){const o={};for(const m of b.matchAll(/--([a-z0-9-]+):\s*([^;]+);/g))o[m[1]]=m[2].trim();return o}
const light=parse(block(':root,\n[data-theme="light"]')), dark={...light,...parse(block('html[data-theme="dark"],\n[data-theme="dark"]'))};
function resolve(t,name,seen=0){let v=t[name];if(v===undefined)return null;if(seen>8)return null;
 let m;
 if((m=v.match(/^rgb\(var\(--([a-z0-9-]+)\)\)$/))){const r=t[m[1]];return '#'+r.split(',').map(x=>(+x).toString(16).padStart(2,'0')).join('')}
 if((m=v.match(/^var\(--([a-z0-9-]+)\)$/)))return resolve(t,m[1],seen+1);
 return v}
function hex(h){h=h.replace('#','');if(h.length==3)h=[...h].map(c=>c+c).join('');return [0,2,4].map(i=>parseInt(h.slice(i,i+2),16))}
function lum(h){const [r,g,b]=hex(h).map(v=>{v/=255;return v<=.03928?v/12.92:((v+.055)/1.055)**2.4});return .2126*r+.7152*g+.0722*b}
function cr(a,b){const A=lum(a),B=lum(b);return (Math.max(A,B)+.05)/(Math.min(A,B)+.05)}
// [fg, bg, min, descripción]
const P=[
 // texto sobre superficies
 ['text-primary','bg-primary',4.5],['text-primary','surface',4.5],['text-primary','bg-secondary',4.5],['text-primary','surface-hover',4.5],['text-primary','surface-sunken',4.5],['text-primary','surface-selected',4.5],
 ['text-secondary','surface',4.5],['text-secondary','bg-primary',4.5],['text-secondary','surface-sunken',4.5],
 ['text-muted','bg-primary',4.5],['text-muted','surface',4.5],['text-muted','bg-secondary',4.5],['text-muted','surface-sunken',4.5],['text-muted','surface-hover',4.5],['text-muted','neutral-bg',4.5],
 ['text-link','surface',4.5],['text-link','bg-primary',4.5],['text-link','bg-secondary',4.5],['text-link','surface-sunken',4.5],['text-link-hover','surface',4.5],['text-link-hover','surface-hover',4.5],
 // controles / UI (3:1)
 ['border-strong','surface',3],['border-strong','bg-primary',3],['focus-ring','surface',3],['focus-ring','bg-primary',3],['primary','surface',3],['primary','bg-primary',3],
 // marca
 ['on-primary','primary',4.5],['on-primary','primary-hover',4.5],['on-primary','primary-active',4.5],['on-primary-soft','primary-soft',4.5],['text-primary','primary-soft',4.5],['on-primary-soft','surface-hover',4.5],['on-primary-soft','surface-selected',4.5],
 ['accent-text','surface',4.5],['accent-text','bg-primary',4.5],
 // estados sólidos
 ['on-success','success',4.5],['on-success','success-hover',4.5],['on-warning','warning',4.5],['on-warning','warning-hover',4.5],['on-danger','danger',4.5],['on-danger','danger-hover',4.5],['on-info','info',4.5],['on-info','info-hover',4.5],
 // estados suaves (alertas, badges, slots, paneles)
 ['success-text','success-bg',4.5],['warning-text','warning-bg',4.5],['danger-text','danger-bg',4.5],['info-text','info-bg',4.5],['neutral-text','neutral-bg',4.5],
 ['success-text','surface',4.5],['danger-text','surface',4.5],['warning-text','surface',4.5],['info-text','surface',4.5],
 ['text-muted','neutral-bg',4.5],
 // bordes de estado como componente (3:1 contra su fondo suave)
 // texto atenuado y principal sobre superficies tintadas (métricas, paneles, drop-zone)
 ...['primary-soft','success-bg','warning-bg','danger-bg','info-bg','neutral-bg','bg-secondary'].flatMap(b=>[['text-muted',b,4.5],['text-primary',b,4.5],['text-secondary',b,4.5]]),
 ['border-strong','primary-soft',3],['text-secondary','primary-soft',4.5],
 // utilidades .text-danger / .text-success / .bg-* de Bootstrap sobre superficies
 ...['surface','bg-primary','bg-secondary','surface-sunken','surface-hover'].flatMap(b=>[['danger',b,4.5],['success',b,4.5],['info',b,4.5]]),
 // punto rojo / contador de chats sin leer (componente 3:1 contra cada fondo donde puede aparecer)
 ['danger','navbar-bg',3],['danger','surface-hover',3],['danger','primary-soft',3],['danger','sidebar-from',3],['danger','sidebar-active-bg',3],
 // enlace «Salir» de la barra admin móvil
 ['danger-text','navbar-bg',4.5],['danger-text','primary-soft',4.5],
 // chat
 ['chat-own-text','chat-own-bg',4.5],['chat-other-text','chat-other-bg',4.5],['chat-meta','bg-secondary',4.5],['chat-meta','surface',4.5],['chat-own-bg','bg-secondary',3],['border-strong','chat-other-bg',3],
 // chrome
 ['text-primary','navbar-bg',4.5],['text-link','navbar-bg',4.5],['text-link-hover','navbar-bg',4.5],
 // gráficos (graphical objects 3:1 sobre surface)
 ...[1,2,3,4,5,6,7,8].map(i=>['chart-'+i,'surface',3]),
 ...['success','warning','danger','info'].map(k=>['chart-'+k,'surface',3]),['chart-text','surface',4.5],
 // invariantes
 ['on-hero','hero-from',4.5],['on-hero-muted','hero-from',4.5],['hero-cta-text','hero-cta-bg',4.5],['on-footer','footer-from',4.5],['on-footer-muted','footer-from',4.5],['on-footer-link','footer-from',4.5],
];
// colores de degradados: extraer primeros/último stop
function stops(t,name){const v=t[name]||'';return [...v.matchAll(/#[0-9A-Fa-f]{6}/g)].map(m=>m[0])}
function get(t,n){
  if(n==='hero-from')return stops(t,'hero-bg').sort((a,b)=>lum(a)-lum(b)).pop();  // el más claro del degradado = peor caso
  if(n==='footer-from')return stops(t,'footer-bg').sort((a,b)=>lum(a)-lum(b)).pop();
  if(n==='sidebar-from')return stops(t,'sidebar-bg').sort((a,b)=>lum(a)-lum(b)).pop();
  const r=resolve(t,n);return r&&r.startsWith('#')?r:null}
// sidebar
P.push(['sidebar-text','sidebar-from',4.5],['sidebar-text-active','sidebar-active-bg',4.5],['sidebar-text','sidebar-active-bg',4.5],['sidebar-accent','sidebar-from',3],['danger-text','sidebar-from',4.5]);
let fail=0,total=0;
for(const [name,t] of [['CLARO',light],['OSCURO',dark]]){
  console.log('\n=== '+name+' ===');
  for(const [f,b,min] of P){const F=get(t,f),B=get(t,b);if(!F||!B){console.log('  ? sin resolver',f,b);continue}
    const r=cr(F,B);total++;const ok=r>=min;if(!ok)fail++;
    if(!ok||process.argv[3]==='-v')console.log(`  ${ok?'ok  ':'FAIL'} ${r.toFixed(2).padStart(5)} (min ${min}) ${f} ${F} / ${b} ${B}`)}
}

const d=dark;for(const n of ['bg-primary','bg-secondary','surface','surface-hover','surface-sunken']){const h=get(d,n);console.log('  dark',n,h,'luma',lum(h).toFixed(4))}
console.log(`\n${total-fail}/${total} pares OK, ${fail} fallan`);
