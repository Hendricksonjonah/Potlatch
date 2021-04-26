<h1><?= $potlatch['title'] ?></h1>
<p><?= $potlatch['description'] ?></p>
<header style="justify-content: flex-start;">
    <h2>Items</h2>
    <?php if($isOwner): ?>
        <button id="addItem" data-id="item">Add Item</button>
    <?php endif; ?>
</header>
<content class="flex">
    <?php if(isset($items ) && !empty($items )): ?>
        <?php foreach($items as $item): ?>
            <card>
                <header><?= $item['title'] ?></header>
                <content><?= $item['description'] ?></content>
                <footer>
                    <?= anchor('auction/'.$item['id'], 'Go To ->') ?>
                </footer>
            </card>
        <?php endforeach; ?>
    <?php else: ?>
        You have not added any Items yet.
    <?php endif; ?>
</content>
<?php if($isOwner): ?>
    <header style="justify-content: flex-start;">
        <h2>Bidders</h2>
        <button id="addBidder" data-id="bidder">Add Bidder</button>
    </header>
    <content>
        <?php if(isset($roster) && !empty($roster)): ?>
            <?php foreach($roster as $bidder): ?>
                <card>
                    <header>
                        <?php if(!is_null($bidder['user_id'])): ?>
                            <?= $bidder['first_name'].' '.$bidder['last_name'] ?>
                        <?php else: ?>
                            Has not joined yet.
                        <?php endif; ?>
                    </header>
                    <content>
                        <!-- Email of User or Invite.  -->
                        <?php if(!is_null($bidder['user_id'])): ?>
                            <div><b>User Email:</b>&nbsp;<?= $bidder['user_email']?></div>
                        <?php else: ?>
                            <div><b>Invite Email:</b>&nbsp;<?= $bidder['invite_email']?></div>
                        <?php endif; ?>
                        <?php if(!is_null($bidder['invite_id'])): ?>
                            <!-- Invite code if user hasn't joined yet.-->
                            <div><b>Invite Code:</b>&nbsp;<?= $bidder['invite_id']?></div>
                        <?php endif; ?>
                        <div><b>Coins:</b>&nbsp;<?= $bidder['coins']?></div>
                    </content>
                </card>
            <?php endforeach; ?>
        <?php else: ?>
            You have not added any Bidders yet.
        <?php endif; ?>
    </content>
<?php endif; ?>
<!-- Overlay -->
<overlay id="overlay_item">
    <content>
        <header>
            <h1>Add Item</h1>
            <button class="close" data-id="item">Close</button>
        </header>
        <?= form_open('potlatch/createItem', ['enctype' => 'multipart/form-data']) ?>
            <label for="title">Title</label>
            <input type="text" id="title" name="title" value="" size="50" />
            <label for="description">Description</label>
            <textarea id="description" name="description" value="" rows="9" maxlength="1000"></textarea>
            <input type="file" enctype="multipart/form-data" name="images[]" multiple/>
            <button type="submit">Create</button>
            <input name="potlatch_id" type="number" value="<?= $potlatch['id'] ?>" hidden/>
        </form>
    </content>
</overlay>
<overlay id="overlay_bidder">
    <content>
        <header>
            <h1>Add Bidder</h1>
            <button class="close" data-id="bidder">Close</button>
        </header>
        <?= form_open('potlatch/addBidder') ?>
            <input type="text" name="email" placeholder="Email" required/>
            <input type="number" name="coins" min="1" placeholder="# of coins" required/>
            <button type="submit">Add</button>
            <input name="potlatch_id" type="number" value="<?= $potlatch['id'] ?>" hidden/>
        </form>
    </content>
</overlay>
<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
<script>
    $('#addItem').click(toggleOverlay);
    $('#overlay_item .close').click(toggleOverlay);

    $('#addBidder').click(toggleOverlay);
    $('#overlay_bidder .close').click(toggleOverlay);

    function toggleOverlay(e){
        let id = $(this).data('id');
        let property = $('#overlay_'+id).css('display');
        if(property == 'none') {
            $('#overlay_'+id).css('display', 'block');
        }else{
             $('#overlay_'+id).css('display', 'none');
        }
    }
</script>