class Comment extends React.Component {
    constructor(props) {
        super(props);
        this.state = {replies: []};
        this.loadReplies = this.loadReplies.bind(this);
        this.getTimestampReadable = this.getTimestampReadable.bind(this);
    }

    render() {
        const replies = [];
        this.state.replies.forEach((comment) => {
            replies.push(<Comment comment={comment} />);
        });
        return (
            <div className="comment_box">
                {this.props.comment != null &&
                    <card className="comment">
                        <header>{`${this.props.comment.first_name} ${this.props.comment.last_name}`}</header>
                        <content>{this.props.comment.comment}</content>
                        <footer>{this.getTimestampReadable(this.props.comment.timestamp)}</footer>
                    </card>
                }
                <div className="replies">
                    {(replies.length > 0 && replies) ||
                    (replies != null &&
                        <button className="load_replies" onClick={this.loadReplies.bind(this)}>Load Replies...</button>)}
                </div>
            </div>
        );
    }

    loadReplies() {
        alert("Load replies");
    }

    getTimestampReadable(timestamp){
        var newDate = new Date();
        newDate.setTime(timestamp*1000);
        return newDate.toUTCString();
    }
}

class CommentDisplay extends React.Component {
    constructor(props) {
        super(props);
    }

    render() {
        const comments = [];
        this.props.comments.forEach((comment) => {
            comments.push(<Comment comment={comment} />);
        });
        return (
            <div className="comment_display">{comments}</div>
        );
    }
}