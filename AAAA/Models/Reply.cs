namespace AAAA.Models
{
    public class Reply
    {
        public int MessageId { get; set; }
        public string ReplyText { get; set; }
        public string ReplierEmail { get; set; }
        public int ReplyId { get; set; }
        public DateTime ReplyDate { get; set; }
    }
}
