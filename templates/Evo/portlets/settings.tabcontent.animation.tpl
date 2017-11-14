<div id="wow-animation" class="tab-pane fade in" role="tabpanel" aria-labelledby="wow-animation-tab">
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="animation-style">Animation style</label>
                <select id="animation-style" name="animation-style" class="form-control">
                    <option value="">None</option>
                    <optgroup label="Attention Seekers">
                        <option value="bounce"{if $properties['animation-style'] === 'bounce'} selected{/if}>bounce</option>
                        <option value="flash"{if $properties['animation-style'] === 'flash'} selected{/if}>flash</option>
                        <option value="pulse"{if $properties['animation-style'] === 'pulse'} selected{/if}>pulse</option>
                        <option value="rubberBand"{if $properties['animation-style'] === 'rubberBand'} selected{/if}>rubberBand</option>
                        <option value="shake"{if $properties['animation-style'] === 'shake'} selected{/if}>shake</option>
                        <option value="swing"{if $properties['animation-style'] === 'swing'} selected{/if}>swing</option>
                        <option value="tada"{if $properties['animation-style'] === 'tada'} selected{/if}>tada</option>
                        <option value="wobble"{if $properties['animation-style'] === 'wobble'} selected{/if}>wobble</option>
                        <option value="jello"{if $properties['animation-style'] === 'jello'} selected{/if}>jello</option>
                    </optgroup>

                    <optgroup label="Bouncing Entrances">
                        <option value="bounceIn"{if $properties['animation-style'] === 'bounceIn'} selected{/if}>bounceIn</option>
                        <option value="bounceInDown"{if $properties['animation-style'] === 'bounceInDown'} selected{/if}>bounceInDown</option>
                        <option value="bounceInLeft"{if $properties['animation-style'] === 'bounceInLeft'} selected{/if}>bounceInLeft</option>
                        <option value="bounceInRight"{if $properties['animation-style'] === 'bounceInRight'} selected{/if}>bounceInRight</option>
                        <option value="bounceInUp"{if $properties['animation-style'] === 'bounceInUp'} selected{/if}>bounceInUp</option>
                    </optgroup>

                    <optgroup label="Fading Entrances">
                        <option value="fadeIn"{if $properties['animation-style'] === 'fadeIn'} selected{/if}>fadeIn</option>
                        <option value="fadeInDown"{if $properties['animation-style'] === 'fadeInDown'} selected{/if}>fadeInDown</option>
                        <option value="fadeInDownBig"{if $properties['animation-style'] === 'fadeInDownBig'} selected{/if}>fadeInDownBig</option>
                        <option value="fadeInLeft"{if $properties['animation-style'] === 'fadeInLeft'} selected{/if}>fadeInLeft</option>
                        <option value="fadeInLeftBig"{if $properties['animation-style'] === 'fadeInLeftBig'} selected{/if}>fadeInLeftBig</option>
                        <option value="fadeInRight"{if $properties['animation-style'] === 'fadeInRight'} selected{/if}>fadeInRight</option>
                        <option value="fadeInRightBig"{if $properties['animation-style'] === 'fadeInRightBig'} selected{/if}>fadeInRightBig</option>
                        <option value="fadeInUp"{if $properties['animation-style'] === 'fadeInUp'} selected{/if}>fadeInUp</option>
                        <option value="fadeInUpBig"{if $properties['animation-style'] === 'fadeInUpBig'} selected{/if}>fadeInUpBig</option>
                    </optgroup>

                    <optgroup label="Flippers">
                        <option value="flip"{if $properties['animation-style'] === 'flip'} selected{/if}>flip</option>
                        <option value="flipInX"{if $properties['animation-style'] === 'flipInX'} selected{/if}>flipInX</option>
                        <option value="flipInY"{if $properties['animation-style'] === 'flipInY'} selected{/if}>flipInY</option>
                    </optgroup>

                    <optgroup label="Lightspeed">
                        <option value="lightSpeedIn"{if $properties['animation-style'] === 'lightSpeedIn'} selected{/if}>lightSpeedIn</option>
                    </optgroup>

                    <optgroup label="Rotating Entrances">
                        <option value="rotateIn"{if $properties['animation-style'] === 'rotateIn'} selected{/if}>rotateIn</option>
                        <option value="rotateInDownLeft"{if $properties['animation-style'] === 'rotateInDownLeft'} selected{/if}>rotateInDownLeft</option>
                        <option value="rotateInDownRight"{if $properties['animation-style'] === 'rotateInDownRight'} selected{/if}>rotateInDownRight</option>
                        <option value="rotateInUpLeft"{if $properties['animation-style'] === 'rotateInUpLeft'} selected{/if}>rotateInUpLeft</option>
                        <option value="rotateInUpRight"{if $properties['animation-style'] === 'rotateInUpRight'} selected{/if}>rotateInUpRight</option>
                    </optgroup>

                    <optgroup label="Sliding Entrances">
                        <option value="slideInUp"{if $properties['animation-style'] === 'slideInUp'} selected{/if}>slideInUp</option>
                        <option value="slideInDown"{if $properties['animation-style'] === 'slideInDown'} selected{/if}>slideInDown</option>
                        <option value="slideInLeft"{if $properties['animation-style'] === 'slideInLeft'} selected{/if}>slideInLeft</option>
                        <option value="slideInRight"{if $properties['animation-style'] === 'slideInRight'} selected{/if}>slideInRight</option>

                    </optgroup>

                    <optgroup label="Zoom Entrances">
                        <option value="zoomIn"{if $properties['animation-style'] === 'zoomIn'} selected{/if}>zoomIn</option>
                        <option value="zoomInDown"{if $properties['animation-style'] === 'zoomInDown'} selected{/if}>zoomInDown</option>
                        <option value="zoomInLeft"{if $properties['animation-style'] === 'zoomInLeft'} selected{/if}>zoomInLeft</option>
                        <option value="zoomInRight"{if $properties['animation-style'] === 'zoomInRight'} selected{/if}>zoomInRight</option>
                        <option value="zoomInUp"{if $properties['animation-style'] === 'zoomInUp'} selected{/if}>zoomInUp</option>
                    </optgroup>

                    <optgroup label="Specials">
                        <option value="hinge"{if $properties['animation-style'] === 'hinge'} selected{/if}>hinge</option>
                        <option value="rollIn"{if $properties['animation-style'] === 'rollIn'} selected{/if}>rollIn</option>
                    </optgroup>

                </select>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="data-wow-duration">Duration</label>
                <input type="text" class="form-control" id="data-wow-duration" name="attr[data-wow-duration]" value="{$properties.attr['data-wow-duration']}">
                <span class="help-block">Change the animation duration.</span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="data-wow-delay">Delay</label>
                <input type="text" class="form-control" id="data-wow-delay" name="attr[data-wow-delay]" value="{$properties.attr['data-wow-delay']}">
                <span class="help-block">Delay before the animation starts.</span>
            </div>
        </div>
        <div class="col-sm-6">
            <div class="form-group">
                <label for="data-wow-offset">Offset</label>
                <input type="text" class="form-control" id="data-wow-offset" name="attr[data-wow-offset]" value="{$properties.attr['data-wow-offset']}">
                <span class="help-block">Distance to start the animation.</span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <div class="form-group">
                <label for="data-wow-iteration">Iteration</label>
                <input type="text" class="form-control" id="data-wow-iteration" name="attr[data-wow-iteration]" value="{$properties.attr['data-wow-iteration']}">
                <span class="help-block">The animation number times is repeated.</span>
            </div>
        </div>
    </div>
</div>