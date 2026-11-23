const t=(t,e={})=>t.replace(/\{(\w+)\}/g,(t,r)=>e[r]??t),e=(e,r,a)=>{const s=r.replace(/-([a-z])/g,(t,e)=>e.toUpperCase());return t(e.getAttribute(r)??e.constructor.defaults?.[s]??"",a)};export{t as f,e as t};
//# sourceMappingURL=text.js.map
