(()=>{
function initCarousel(root){
  const track = root.querySelector('.pt-team-track');
  const prev = root.querySelector('.pt-nav.prev');
  const next = root.querySelector('.pt-nav.next');
  const dots = root.querySelector('#pt-dots');
  if(!track) return;
  const cards = Array.from(track.children);
  if(cards.length===0) return;

  const GAP = 16; // must match CSS gap
  let autoplayTimer = null;
  let isHovering = false;
  let userPausedUntil = 0;

  // Build dots
  dots.innerHTML = "";
  cards.forEach((_,i)=>{
    const b=document.createElement('button');
    if(i===0) b.classList.add('active');
    b.addEventListener('click', ()=>{
      stopAutoplayTemporarily();
      scrollToIndex(i);
    });
    dots.appendChild(b);
  });

  function cardWidth(){
    const cs=getComputedStyle(track);
    const w=parseFloat(cs.getPropertyValue('--card-w'));
    return (isFinite(w) && w>0) ? w : 260;
  }
  function totalContentWidth(){
    return cards.length*cardWidth() + (cards.length-1)*GAP;
  }
  function currentIndexApprox(){
    // Approximate index by scrollLeft spacing
    return Math.round(track.scrollLeft/(cardWidth()+GAP));
  }
  function realCenterIndex(){
    // Find the card whose center is closest to the viewport center
    const trackRect = track.getBoundingClientRect();
    const viewCenter = trackRect.width / 2;
    let bestIdx = 0, bestDist = Infinity;
    cards.forEach((card, i) => {
      const rect = card.getBoundingClientRect();
      const center = (rect.left + rect.right)/2 - trackRect.left;
      const d = Math.abs(center - viewCenter);
      if (d < bestDist){ bestDist = d; bestIdx = i; }
    });
    return bestIdx;
  }
  function scrollToIndex(i){
    const max = Math.max(0, cards.length-1);
    const clamped = Math.min(max, Math.max(0, i));
    track.scrollTo({left: clamped*(cardWidth()+GAP), behavior:'smooth'});
  }
  function setActiveDot(i){
    Array.from(dots.children).forEach((d,ix)=>d.classList.toggle('active', ix===i));
  }
  function recomputeCentering(){
    const needCenter = totalContentWidth() <= track.clientWidth;
    track.classList.toggle('is-centered', needCenter);
  }

  // Visibility falloff based on distance from viewport center
  function applyFocus(){
    const trackRect = track.getBoundingClientRect();
    const viewCenter = trackRect.width / 2;

    // Compute distances
    const distances = cards.map((card, i) => {
      const rect = card.getBoundingClientRect();
      const center = (rect.left + rect.right)/2 - trackRect.left;
      return { i, d: Math.abs(center - viewCenter) };
    });

    // Rank by distance
    distances.sort((a,b)=>a.d-b.d);
    const visMap = new Map();
    const levels = [1.0, 0.85, 0.60, 0.35]; // 0th, 1st, 2nd, 3rd closest
    distances.forEach((o, rank) => {
      const vis = (rank < levels.length) ? levels[rank] : 0.15;
      visMap.set(o.i, vis);
    });

    // Apply visibility and set active dot to true center
    distances.sort((a,b)=>a.i-b.i); // back to index order
    cards.forEach((card, idx)=>{
      const vis = visMap.get(idx) ?? 0.15;
      card.style.setProperty('--vis', String(vis));
    });

    setActiveDot(realCenterIndex());
  }

  function onResize(){
    recomputeCentering();
    applyFocus();
  }

  function startAutoplay(){
    stopAutoplay();
    autoplayTimer = setInterval(()=>{
      const now = Date.now();
      if (isHovering || now < userPausedUntil) return;
      const idx = realCenterIndex();
      const nextIdx = (idx+1) % cards.length;
      scrollToIndex(nextIdx);
    }, 4000);
  }
  function stopAutoplay(){
    if (autoplayTimer){ clearInterval(autoplayTimer); autoplayTimer=null; }
  }
  function stopAutoplayTemporarily(ms=8000){
    userPausedUntil = Date.now() + ms;
  }

  // Listeners
  prev.addEventListener('click', ()=>{
    stopAutoplayTemporarily();
    scrollToIndex(Math.max(0, realCenterIndex()-1));
  });
  next.addEventListener('click', ()=>{
    stopAutoplayTemporarily();
    scrollToIndex(Math.min(cards.length-1, realCenterIndex()+1));
  });
  track.addEventListener('scroll', ()=>{
    if (!track._raf){
      track._raf = requestAnimationFrame(()=>{
        track._raf = null;
        applyFocus();
      });
    }
  });
  root.addEventListener('mouseenter', ()=>{ isHovering=true; });
  root.addEventListener('mouseleave', ()=>{ isHovering=false; });
  window.addEventListener('resize', onResize);

  // Init
  recomputeCentering();
  applyFocus();
  startAutoplay();
}

window.addEventListener('load', ()=>{
  document.querySelectorAll('.pt-team-wrap').forEach(initCarousel);
});
})();