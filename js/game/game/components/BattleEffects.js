function BattleEffects (target) {
	
	this.mirage = function () {
		if (target.__mirage){
			target.__mirage.remove();
			target.__mirage = null;
		}
		target.__mirage = target.create('div');
		target.__mirage.innerHTML = target.innerHTML;
		target.__mirage.style.opacity = 0;
		target.__mirage.style.WebkitTransformOrigin = '0% 100%';
		var a = new Animate(target.__mirage);
		if (target.opposite){
			a.frame(0, {opacity: 0.8, WebkitTransform: 'translate(0%, -100%) scale(1, 1)'});
			a.frame(300, {opacity: 0, WebkitTransform: 'translate(0%, -100%) scale(1.25, 1.4)'});
		}
		else {
			a.frame(0, {opacity: 0.8, WebkitTransform: 'translate(0%, -100%) scale(1, 1)'});
			a.frame(300, {opacity: 0, WebkitTransform: 'translate(0%, -100%) scale(1.25, 1.4)'});
		}
		a.onfinish(function () {
			if (target.__mirage && target.__mirage.remove){
				target.__mirage.remove();
				target.__mirage = null;
			}
		});
		
		return a;
	};
	
	this.shake = function () {
		var a = new Animate(target);
		a.iterate(4);
		if (target.opposite){
			a.frame(0, {WebkitTransform: 'rotate(0deg) scaleX(-1)'});
		a.frame(30, {WebkitTransform: 'rotate(4deg) scaleX(-1)'});
		a.frame(30, {WebkitTransform: 'rotate(0deg) scaleX(-1)'});
		a.frame(30, {WebkitTransform: 'rotate(-4deg) scaleX(-1)'});
		}
		else {
			a.frame(0, {WebkitTransform: 'rotate(0deg)'});
			a.frame(30, {WebkitTransform: 'rotate(4deg)'});
			a.frame(30, {WebkitTransform: 'rotate(0deg)'});
			a.frame(30, {WebkitTransform: 'rotate(-4deg)'});
		}
		return a;
	};
}