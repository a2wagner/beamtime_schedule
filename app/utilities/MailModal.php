<?php

class MailModal
{
	/**
	 * The modal content to compose emails
	 *
	 * @var string
	 */
	protected $modal = '
	    <div class="modal fade mail-modal-lg [LABEL]" tabindex="-1" role="dialog" aria-labelledby="[LABEL]">
	      <div class="modal-dialog modal-lg" role="document">
	        <div class="modal-content">
	          <div class="modal-header">
	            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
	            <h3 class="modal-title" id="[LABEL]">[TITLE]</h3>
	          </div>
	          <div class="modal-body"><form>
	            <fieldset>
                  <div class="form-group">
                    <label for="subject" class="control-label">Subject</label>
                    <div class="col-lg">
                      <input id="subject" type="text" name="subject" class="form-control" placeholder="Mail Subject" required="required">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="content" class="control-label">Content</label>
                    <div class="col-lg">
                      <textarea id="content" name="content" class="form-control" placeholder="Message content&#10;If you use the string [USER] it will be replaced by the user name" rows="5" required="required"></textarea>
                    </div>
                  </div>
                </fieldset></form>
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
	 * Print the mail compositor within a modal
	 */
	public function modal($label = 'mail-modal', $title = 'Write Email')
	{
		echo str_replace(array('[LABEL]', '[TITLE]'), array($label, $title), $this->modal);
	}
}
