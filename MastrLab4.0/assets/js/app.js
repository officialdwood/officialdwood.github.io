(function(){
  const $ = (sel, el=document)=> el.querySelector(sel);
  const $$ = (sel, el=document)=> [...el.querySelectorAll(sel)];

  const presetMap = {
    vocals: ["Vocals","Vocals Rich","Chorus","Reverb","Natural Reverb","Large Reverb","Medium Reverb","Echo Chamber"],
    song: ["Punch","Bass","Bass Punch","Highs","Limit","Radio Master","Bright Pop","Pop","Hip Hop","Boom Bap","Lofi"],
    sample: ["Reverb","Medium Reverb","Large Reverb","Echo","Echo Chamber","Bright","Lower End Boost"],
    track: ["Pop","Bright Pop","Limiter","Radio","Hip Hop","Boom Bap","Lofi","Lower end punch"]
  };

  const formatTime = s => {
    if (!isFinite(s)) return "0:00";
    const m = Math.floor(s/60);
    const ss = Math.floor(s%60).toString().padStart(2,'0');
    return `${m}:${ss}`;
  };

  let wavesurfer, timeline;
  let audioCtx;
  let srcNode;
  let nodes = {};
  let currentBuffer;

  function setupPresets(){
    const modeSel = $("#ml-mode");
    const presetSel = $("#ml-preset");
    function populate(){
      const mode = modeSel.value;
      presetSel.innerHTML = "";
      presetMap[mode].forEach(p=>{
        const o = document.createElement('option');
        o.value = p.toLowerCase().replace(/\s+/g,'-');
        o.textContent = p;
        presetSel.appendChild(o);
      });
    }
    modeSel.addEventListener('change',()=>{
      populate();
      applyPreset();
    });
    presetSel.addEventListener('change', applyPreset);
    populate();
  }

  function makeKnobs(){
    $$(".ml-knob").forEach(knob=>{
      const tick = document.createElement('div');
      tick.className = 'tick';
      knob.appendChild(tick);
      const label = document.createElement('div');
      label.className='label';
      label.textContent = knob.dataset.default ?? "0";
      knob.appendChild(label);

      const min = parseFloat(knob.dataset.min);
      const max = parseFloat(knob.dataset.max);
      const step = parseFloat(knob.dataset.step || "1");
      let value = parseFloat(knob.dataset.default || "0");
      setAngleFromValue(value);

      let dragging = false;
      let lastY = 0;
      knob.addEventListener('pointerdown', (e)=>{
        dragging = true; lastY = e.clientY; knob.setPointerCapture(e.pointerId);
      });
      knob.addEventListener('pointermove', (e)=>{
        if(!dragging) return;
        const dy = lastY - e.clientY;
        lastY = e.clientY;
        const delta = dy * (max-min) / 300; // sensitivity
        value = clamp(value + delta, min, max);
        setAngleFromValue(value);
        label.textContent = value.toFixed( (step<1)?2:0 );
        onKnobChange(knob.dataset.param, value);
      });
      knob.addEventListener('pointerup', ()=> dragging=false);
      knob.addEventListener('pointercancel', ()=> dragging=false);

      function setAngleFromValue(v){
        const ratio = (v - min) / (max - min);
        const angle = -135 + ratio * 270; // sweep 270deg
        tick.style.transform = `translateX(-50%) rotate(${angle}deg)`;
        knob.style.boxShadow = `inset 0 0 0 2px #0f2b33, 0 0 22px rgba(25,231,255, ${0.2 + ratio*0.6})`;
      }
    });
  }

  function clamp(v,min,max){ return v<min?min:(v>max?max:v); }

  function buildGraph(){
    audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
    nodes.input = audioCtx.createGain();
    nodes.gain = audioCtx.createGain();
    nodes.eqLow = audioCtx.createBiquadFilter(); nodes.eqLow.type='lowshelf'; nodes.eqLow.frequency.value = 120;
    nodes.eqMid = audioCtx.createBiquadFilter(); nodes.eqMid.type='peaking'; nodes.eqMid.frequency.value = 1600; nodes.eqMid.Q.value = 1.0;
    nodes.eqHigh = audioCtx.createBiquadFilter(); nodes.eqHigh.type='highshelf'; nodes.eqHigh.frequency.value = 8000;
    nodes.deesser = audioCtx.createBiquadFilter(); nodes.deesser.type='peaking'; nodes.deesser.frequency.value = 6500; nodes.deesser.Q.value=3.5; nodes.deesser.gain.value=0;
    nodes.comp = audioCtx.createDynamicsCompressor();
    nodes.comp.attack.value = 0.003; nodes.comp.release.value = 0.2; nodes.comp.ratio.value = 3; nodes.comp.threshold.value = -24; nodes.comp.knee.value = 20;

    nodes.limiter = audioCtx.createDynamicsCompressor();
    nodes.limiter.attack.value = 0.001; nodes.limiter.release.value=0.08; nodes.limiter.ratio.value=20; nodes.limiter.threshold.value=-1; nodes.limiter.knee.value=0.0;

    nodes.output = audioCtx.createGain();

    // chain
    nodes.input.connect(nodes.eqLow).connect(nodes.eqMid).connect(nodes.eqHigh).connect(nodes.deesser).connect(nodes.comp).connect(nodes.gain).connect(nodes.limiter).connect(nodes.output).connect(audioCtx.destination);
  }

  function onKnobChange(param, value){
    switch(param){
      case 'gain': nodes.gain.gain.value = Math.pow(10, value/20); break;
      case 'eqLow': nodes.eqLow.gain.value = value; break;
      case 'eqMid': nodes.eqMid.gain.value = value; break;
      case 'eqHigh': nodes.eqHigh.gain.value = value; break;
      case 'comp': nodes.comp.ratio.value = 1 + value*19; nodes.comp.threshold.value = -36 + value*12; break;
      case 'limit': nodes.limiter.threshold.value = value; break;
      case 'deesser': nodes.deesser.gain.value = -value*12; break;
    }
  }

  function applyPreset(){
    const mode = $("#ml-mode").value;
    const preset = $("#ml-preset").value;
    const ai = $("#ml-ai-toggle").checked;

    // Start with AI defaults
    if(ai){
      onKnobChange('gain',0.0);
      onKnobChange('eqLow', 2.0);
      onKnobChange('eqMid', 0.5);
      onKnobChange('eqHigh', 1.8);
      onKnobChange('comp', 0.4);
      onKnobChange('limit', -1.2);
      onKnobChange('deesser', 0.25);
    }
    const P = (p)=>p.toLowerCase();
    if(mode==='song'){
      if(P(preset).includes('punch')){ onKnobChange('comp',0.6); onKnobChange('gain',1.5); }
      if(P(preset).includes('bass')){ onKnobChange('eqLow',5.0); }
      if(P(preset).includes('bright')){ onKnobChange('eqHigh',4.0); }
      if(P(preset).includes('hip')||P(preset).includes('boom')){ onKnobChange('eqLow',3.5); onKnobChange('eqHigh',1.2); onKnobChange('comp',0.5); }
      if(P(preset).includes('lofi')){ onKnobChange('eqHigh',-2.0); onKnobChange('limit',-3.0); }
      if(P(preset).includes('limit')||P(preset).includes('radio')){ onKnobChange('limit',-0.8); onKnobChange('comp',0.55); }
    }
    if(mode==='vocals'){
      if(P(preset).includes('rich')){ onKnobChange('eqHigh',3.0); onKnobChange('deesser',0.3); }
      if(P(preset).includes('reverb')){ /* Reverb stub in render only */ }
      if(P(preset).includes('echo')){ /* Echo stub in render only */ }
    }
  }

  async function loadFile(file){
    $("#ml-loader").classList.remove('hidden');
    $("#ml-loader-pct").textContent = "0%";
    $("#ml-loader-progress").style.width = "0%";
    try{
      const arrayBuffer = await file.arrayBuffer();
      for(let i=1;i<=5;i++){ // fake upload progression for UX
        await new Promise(r=>setTimeout(r,60));
        $("#ml-loader-pct").textContent = `${i*10}%`;
        $("#ml-loader-progress").style.width = `${i*10}%`;
      }
      audioCtx = audioCtx || new (window.AudioContext || window.webkitAudioContext)();
      currentBuffer = await audioCtx.decodeAudioData(arrayBuffer.slice(0));
      $("#ml-loader-pct").textContent = "100%";
      $("#ml-loader-progress").style.width = "100%";
    } finally {
      setTimeout(()=>$("#ml-loader").classList.add('hidden'), 200);
    }
    initWavesurfer(file);
    $("#ml-duration").textContent = formatTime(currentBuffer.duration);
  }

  function initWavesurfer(file){
    if(wavesurfer){ wavesurfer.destroy(); }
    const color = getComputedStyle(document.documentElement).getPropertyValue('--ml-cyan').trim() || '#19e7ff';
    wavesurfer = WaveSurfer.create({
      container: '#ml-waveform',
      waveColor: 'rgba(25,231,255,0.25)',
      progressColor: color,
      cursorColor: color,
      barWidth: 2,
      height: 140,
      url: URL.createObjectURL(file)
    });
    timeline = wavesurfer.registerPlugin(WaveSurfer.Timeline.create({ container: '#ml-timeline', primaryLabelInterval: 5, secondaryLabelInterval: 1 }));

    wavesurfer.on('audioprocess', ()=> $("#ml-current").textContent = formatTime(wavesurfer.getCurrentTime()));
    wavesurfer.on('ready', ()=> $("#ml-duration").textContent = formatTime(wavesurfer.getDuration()));
    wavesurfer.on('finish', ()=> $("#ml-current").textContent = formatTime(0));
  }

  function connectSource(){
    if(srcNode){ try{srcNode.disconnect();}catch(e){} }
    srcNode = audioCtx.createBufferSource();
    srcNode.buffer = currentBuffer;
    srcNode.connect(nodes.input);
    return srcNode;
  }

  async function renderToBuffer(){
    const sampleRate = 44100;
    const offline = new OfflineAudioContext(currentBuffer.numberOfChannels, currentBuffer.length, sampleRate);

    // Build offline graph
    const input = offline.createBufferSource();
    input.buffer = currentBuffer;

    const gain = offline.createGain();
    const eqLow = offline.createBiquadFilter(); eqLow.type='lowshelf'; eqLow.frequency.value=120; eqLow.gain.value = nodes.eqLow.gain.value;
    const eqMid = offline.createBiquadFilter(); eqMid.type='peaking'; eqMid.frequency.value=1600; eqMid.Q.value=1.0; eqMid.gain.value = nodes.eqMid.gain.value;
    const eqHigh = offline.createBiquadFilter(); eqHigh.type='highshelf'; eqHigh.frequency.value=8000; eqHigh.gain.value = nodes.eqHigh.gain.value;
    const deesser = offline.createBiquadFilter(); deesser.type='peaking'; deesser.frequency.value=6500; deesser.Q.value=3.5; deesser.gain.value = nodes.deesser.gain.value;
    const comp = offline.createDynamicsCompressor();
    comp.attack.value = nodes.comp.attack.value; comp.release.value=nodes.comp.release.value; comp.ratio.value=nodes.comp.ratio.value; comp.threshold.value=nodes.comp.threshold.value; comp.knee.value=nodes.comp.knee.value;
    const limiter = offline.createDynamicsCompressor();
    limiter.attack.value=nodes.limiter.attack.value; limiter.release.value=nodes.limiter.release.value; limiter.ratio.value=nodes.limiter.ratio.value; limiter.threshold.value=nodes.limiter.threshold.value; limiter.knee.value=0;

    gain.gain.value = nodes.gain.gain.value;

    input.connect(eqLow).connect(eqMid).connect(eqHigh).connect(deesser).connect(comp).connect(gain).connect(limiter).connect(offline.destination);
    input.start();
    const rendered = await offline.startRendering();
    return rendered;
  }

  function bufferToWavBlob(buffer){
    const numChannels = buffer.numberOfChannels;
    const length = buffer.length * numChannels * 2 + 44;
    const arrayBuffer = new ArrayBuffer(length);
    const view = new DataView(arrayBuffer);

    function writeString(view, offset, str){
      for(let i=0;i<str.length;i++) view.setUint8(offset+i, str.charCodeAt(i));
    }
    let pos = 0;
    writeString(view, pos, 'RIFF'); pos += 4;
    view.setUint32(pos, length-8, true); pos += 4;
    writeString(view, 'WAVE', 0); pos += 4;
    writeString(view, 'fmt ', 0); pos += 4;
    view.setUint32(pos, 16, true); pos += 4;
    view.setUint16(pos, 1, true); pos += 2;
    view.setUint16(pos, numChannels, true); pos += 2;
    view.setUint32(pos, buffer.sampleRate, true); pos += 4;
    view.setUint32(pos, buffer.sampleRate * numChannels * 2, true); pos += 4;
    view.setUint16(pos, numChannels * 2, true); pos += 2;
    view.setUint16(pos, 16, true); pos += 2;
    writeString(view, 'data', 0); pos += 4;
    view.setUint32(pos, length - 44, true); pos += 4;

    const data = new Float32Array(buffer.length * numChannels);
    for (let channel = 0; channel < numChannels; channel++) {
      data.set(buffer.getChannelData(channel), channel * buffer.length);
    }
    let offset = 44;
    for (let i=0; i<data.length; i++) {
      const s = Math.max(-1, Math.min(1, data[i]));
      view.setInt16(offset, s < 0 ? s * 0x8000 : s * 0x7FFF, true);
      offset += 2;
    }
    return new Blob([arrayBuffer], {type: 'audio/wav'});
  }

  function wavToMp3Blob(wavBlob){
    return new Promise((resolve)=>{
      const reader = new FileReader();
      reader.onload = function(){
        const arrayBuffer = reader.result;
        const view = new DataView(arrayBuffer);
        const numChannels = view.getUint16(22, true);
        const sampleRate = view.getUint32(24, true);
        const bytesPerSample = view.getUint16(34, true) / 8;
        const dataStart = 44;
        const samples = new Int16Array(arrayBuffer, dataStart);

        const mp3encoder = new lamejs.Mp3Encoder(numChannels, sampleRate, 192);
        const chunkSize = 1152;
        let mp3Data = [];
        for (let i = 0; i < samples.length; i += chunkSize*numChannels) {
          let left = samples.subarray(i, i + chunkSize);
          let right = numChannels===2 ? samples.subarray(i + chunkSize, i + 2*chunkSize) : null;
          const mp3buf = numChannels===2 ? mp3encoder.encodeBuffer(left, right) : mp3encoder.encodeBuffer(left);
          if (mp3buf.length > 0) mp3Data.push(mp3buf);
        }
        const end = mp3encoder.flush();
        if (end.length > 0) mp3Data.push(end);
        resolve(new Blob(mp3Data, {type: 'audio/mpeg'}));
      };
      reader.readAsArrayBuffer(wavBlob);
    });
  }

  document.addEventListener('DOMContentLoaded', ()=>{
    setupPresets();
    makeKnobs();
    buildGraph();

    $("#ml-load").addEventListener('click', async()=>{
      const f = $("#ml-file").files[0];
      if(!f) return alert("Select a .wav or .mp3 file first.");
      await loadFile(f);
    });

    $("#ml-play").addEventListener('click', async()=>{
      if(!currentBuffer){ return alert("Load an audio file first."); }
      if(!audioCtx) buildGraph();
      const node = connectSource();
      node.start();
      $("#ml-current").textContent = "0:00";
    });

    $("#ml-stop").addEventListener('click', ()=>{
      if(wavesurfer) wavesurfer.pause();
      if(audioCtx && audioCtx.state==='running'){ audioCtx.suspend(); }
    });

    $("#ml-render").addEventListener('click', async()=>{
      if(!currentBuffer) return alert("Load an audio file first.");
      const b1 = await renderToBuffer();
      const wavBlob = bufferToWavBlob(b1);
      let blob = wavBlob;
      const fmt = $("#ml-format").value;
      if(fmt==='mp3'){
        blob = await wavToMp3Blob(wavBlob);
      }
      const a = document.createElement('a');
      a.href = URL.createObjectURL(blob);
      a.download = `mastrlab_master.${fmt}`;
      document.body.appendChild(a);
      a.click();
      a.remove();
    });

    $("#ml-ai-toggle").addEventListener('change', applyPreset);
  });
})();