namespace AAAA.Models
{
    public class Tender
    {
        public int TenderId { get; set; }
        public string TenderName { get; set; }
        public string DrugName { get; set; }
        public int Quantity { get; set; }
        public string Specifications { get; set; }
        public DateTime SubmissionDeadline { get; set; }
        public string ContractTerms { get; set; }
        public string Status { get; set; }
    }


}
