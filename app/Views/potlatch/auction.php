<section>
    <carousel>
        <?php if(isset($images) && count($images) > 1): ?>
            <button id="left"><</button>
        <?php endif; ?>
        <images>
            <?php if(isset($images)): ?>
                <?php foreach($images as $filename): ?>
                    <?= img('image/item/'.$item['potlatch_id'].'/'.$item['id'].'/'.$filename) ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </images>
        <?php if(isset($images) && count($images) > 1): ?>
            <button id="right">></button>
        <?php endif; ?>
    </carousel>
    <?= form_open('auction/bid') ?>
        <h2><?= $item['title'] ?></h2>
        <div>
            <?php if(isset($highestBid['amount'])): ?>
                <p>Current Bid: $<?= $highestBid['amount'] ?></p>
                <?php if($canBid): ?>
                    <input name="bid" type="number" min="<?= $highestBid['amount']+1 ?>" placeholder="Bid starting at: <?= $highestBid['amount']+1 ?>" required/>
                <?php endif; ?>
            <?php else: ?>
                <p>Starting Bid: $1</p>
            <?php endif; ?>
        </div>
        <?php if($canBid): ?>
            <button type="submit">Place Bid</button>
        <?php else: ?>
            <button disabled>
                <?php if($isHighestBidder): ?>
                    Highest Bidder
                <?php else: ?>
                    Bid Disabled
                <?php endif; ?>
            </button>
        <?php endif; ?>
        <input name="item_id" type="number" value="<?= $item['id'] ?>" hidden/>
        <input name="highestBid" type="number" value="<?= $highestBid['amount']?>" hidden/>
    </form>
    <br>
    <br>
    <?= form_open('auction/comment') ?>
        <input name="comment" type="text" required/>
        <input name="item_id" type="number" value="<?= $item['id'] ?>" hidden/>
        <button type="submit">Post</button>
    </form>
    <?php if(isset($comments )): ?>
        <?php foreach($comments as $comment): ?>
            <card>
                <header></header>
                <content><?= $comment['comment'] ?></content>
                <footer>
                </footer>
            </card>
        <?php endforeach; ?>
    <?php else: ?>
        There are no comments yet.
    <?php endif; ?>

</section>
<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
<script>
    $('carousel #left').click(function(){
        $('carousel images > img:last-child').prependTo('carousel images');
    });

    $('carousel #right').click(function(){
        $('carousel images > img:first-child').appendTo('carousel images');
    });
</script>
