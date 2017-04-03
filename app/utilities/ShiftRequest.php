<?php

class ShiftRequest
{
	/**
	 * A modal including the shift request
	 *
	 * @var string
	 */
	protected $modal = '
	    <div class="modal fade request-modal-lg [LABEL]" tabindex="-1" role="dialog" aria-labelledby="[LABEL]">
	      <div class="modal-dialog modal-lg">
	        <div class="modal-content">
	          <div class="modal-header">
	            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	            <h3 class="modal-title" id="[LABEL]">Shift Request</h3>
	          </div>
	          <div class="modal-body">
	            [REQUEST]
	          </div>
	          <div class="modal-footer">
	            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
	            <button type="submit" class="btn btn-primary">Submit</button>
	          </div>
	        </div>
	      </div>
	    </div>
	    ';

	/**
	 * Print the rating guide
	 */
	public function show()
	{
		echo $this->guide;
	}

	/**
	 * Print the shift request within a modal
	 */
	public function modal($label = 'shift-request-modal', $request = 'Do you want to send a shift request?')
	{
		echo str_replace(array('[LABEL]', '[REQUEST]'), array($label, $request), $this->modal);
	}
}
